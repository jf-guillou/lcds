<?php

namespace app\models\types;

use ICal\ICal;
use app\models\ContentType;
use yii\helpers\Url;

/**
 * This is the model class for Agenda content type.
 */
class Agenda extends ContentType
{
    const BASE_CACHE_TIME = 7200; // 2 hours
    const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    const HOURS = [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18];

    public $name = 'Agenda';
    public $description = 'Display an agenda from an ICal feed.';
    public $html = '<img class="agenda" src="%data%" />';
    public $css = '%field% { text-align: center; vertical-align: middle; } %field% img { height: 100%; width: 100%; object-fit: contain; }';
    public $input = 'url';
    public $output = 'url';
    public $usable = true;
    public $preview = '@web/images/agenda.preview.jpg';

    private $opts;
    private $img;
    private $font = 3;
    private $fontSize = 20;
    private $fontFile = 'arial';
    private $strW;
    private $strH;
    private $color;
    private $dayStep;
    private $hourStep;
    private $headerHeight;
    private $leftBlockWidth;

    /**
     * {@inheritdoc}
     */
    public function processData($data)
    {
        $filename = $this->name.md5($data).'.png';
        if (self::hasCache($data)) {
            return Url::to(Media::getWebPath().$filename);
        }

        $this->genImage($data, $filename);

        return Url::to(Media::getWebPath().$filename);
    }

    /**
     * Render an image based on Agenda feed.
     *
     * @param string $url      feed url
     * @param string $filename storage file name
     */
    public function genImage($url, $filename)
    {
        // Fetch content from cache if possible
        $content = self::fromCache($url);
        if (!$content) {
            $content = self::downloadContent($url);
            self::toCache($url, $content);
        }

        $this->opts = \Yii::$app->params['agenda'];

        // Init ICal parser
        $ical = new ICal();
        $ical->initString($content);

        // Retrieve event for this week only
        $events = $ical->eventsFromRange(self::DAYS[0].' this week', self::DAYS[count(self::DAYS) - 1].' this week 23:59');
        if (!is_array($events) || !count($events)) {
            return;
        }

        // Draw base calendar structure
        $this->initCalendar();

        // Init timezone converter
        $utcTz = new \DateTimeZone($this->opts['calendarTimezone']);
        $localTz = new \DateTimeZone(ini_get('date.timezone'));
        $hasMultipleLocations = false;

        $blocks = [];
        $prevLocation = null;
        // Build event blocks array
        foreach ($events as $e) {
            // Convert timezones
            $start = new \DateTime('@'.$e->dtstart_array[2], $utcTz);
            $start->setTimeZone($localTz);
            $end = new \DateTime('@'.$e->dtend_array[2], $utcTz);
            $end->setTimeZone($localTz);

            // Event info
            $dow = $start->format('w') - 1;
            $startHour = $start->format('G') + ($start->format('i') / 60.0) - self::HOURS[0];
            $endHour = $end->format('G') + ($end->format('i') / 60.0) - self::HOURS[0];
            $duration = $endHour - $startHour;
            $name = self::filter(html_entity_decode($e->summary, ENT_QUOTES), 'name');
            $location = self::filter(html_entity_decode($e->location, ENT_QUOTES), 'location');
            $desc = array_values(array_filter(array_map('self::filter', explode('\n', html_entity_decode($e->description, ENT_QUOTES)))));
            $teachers = [];
            $groups = [];

            // Guess groups and teachers names from description
            foreach ($desc as $l) {
                if ((strlen($l) >= 8 || strpos($l, '.') !== false) && preg_match('/\d|LP/', $l) == 0) {
                    $teachers[] = $l;
                } elseif (strlen($l) < 20 || preg_match('/LP/', $l) == 1) {
                    $groups[] = $l;
                }
            }
            sort($groups);
            $group = implode(', ', $groups);

            $text = [
                $name,
                $location,
                $group,
                count($teachers) && $this->opts['displayTeachers'] ? implode(', ', $teachers) : null,
            ];

            // Create colors based on group name
            // %160 + 95 make colors brighter
            if (!array_key_exists($group, $this->color)) {
                $groupHash = md5($group);
                $this->color[$group] = imagecolorallocate(
                    $this->img,
                    hexdec(substr($groupHash, 0, 2)) % 160 + 95,
                    hexdec(substr($groupHash, 2, 2)) % 160 + 95,
                    hexdec(substr($groupHash, 4, 2)) % 160 + 95
                );
            }

            // Sets global multiple locations, to enable width divisers
            // A single location cannot be used by multiple groups at the same time
            if ($prevLocation !== null && $location != $prevLocation) {
                $hasMultipleLocations = true;
            }
            $prevLocation = $location;

            // Init event block
            $blocks[] = [
                'uid' => $e->uid,
                'day' => $dow,
                'startHour' => $startHour,
                'endHour' => $endHour,
                'duration' => $duration,
                'text' => $text,
                'group' => $group,
                'groups' => $groups,
                'bgColor' => $group,
                'borderColor' => 'black',
                'textColor' => 'black',
                'diviser' => 1,
                'position' => 0,
            ];
        }

        // Sort blocks by day and group
        usort($blocks, function ($a, $b) {
            if ($a['day'] != $b['day']) {
                return $a['day'] - $b['day'];
            }

            return $a['group'] > $b['group'] ? 1 : -1;
        });

        // Lookup groups and assign them weights used for block width division
        $diviserWeight = [];
        foreach ($blocks as $b) {
            foreach ($b['groups'] as $g) {
                $w = 1;
                if (preg_match('/\s[a-d]\ss?\d-s?\d$/i', $g)) { // Groupe A S1-S2
                    $w = 2;
                } elseif (preg_match('/s?\d-s?\d$/i', $g)) { // S1-S2
                    $w = 1;
                } elseif (preg_match('/[\s\d][a-d]$/i', $g)) { // Groupe A
                    $w = 2;
                } elseif (preg_match('/^\d{2}$/i', $g)) { // 12
                    $w = 2;
                } elseif (preg_match('/groupe?\s\d$/i', $g)) { // Groupe 1
                    $w = 2;
                } elseif (preg_match('/\d$/i', $g)) { // 123
                    $w = 3;
                }
                if (!array_key_exists($w, $diviserWeight)) {
                    $diviserWeight[$w] = [];
                }
                if (!in_array($g, $diviserWeight[$w])) {
                    $diviserWeight[$w][] = $g;
                    sort($diviserWeight[$w]);
                }
            }
        }

        // Weights 2nd filter pass
        // Uses names to guess group children
        while (true) {
            $hasMoved = false;
            // Check weights
            foreach ($diviserWeight as $weight => $groups) {
                $shortestName = min($groups);
                $thisHasMoved = false;
                foreach ($groups as $i => $g) {
                    if ($shortestName != $g && preg_match('/'.$shortestName.'/', $g)) {
                        unset($diviserWeight[$weight][$i]);
                        $diviserWeight[$weight + 1][] = $g;
                        $hasMoved = true;
                        $thisHasMoved = true;
                    }
                }
                if ($thisHasMoved) {
                    break;
                }
            }
            if (!$hasMoved) {
                break;
            }
        }

        ksort($diviserWeight);

        // Check if room planning (never overlaps) to allow full width on different groups
        if (($forceSingleDiv = !$hasMultipleLocations)) {
            $blocksCount = count($blocks);
            foreach ($blocks as $i => $b) {
                for ($j = $i + 1; $j < $blocksCount; ++$j) {
                    if (self::overlap($b, $blocks[$j])) {
                        $forceSingleDiv = false;
                        break 2;
                    }
                }
            }
        }

        // Set block width diviser and position based on weights
        $divisers = [];
        $positions = [];
        $parentDiv = null;
        foreach ($diviserWeight as $weight => $groups) {
            $weightPos = 0;
            if ($forceSingleDiv) {
                // Room planning, always full width
                $div = 1;
            } else {
                // Divide width by count of groups using this weight
                $div = count($groups);
                if ($parentDiv != null && $div < $parentDiv * 2) {
                    // Subgroups should always have at least half the parent group width
                    $div = $parentDiv * 2;
                }
            }

            // Assign divisers and positions
            foreach ($groups as $group) {
                $divisers[$group] = $div;

                // Position catchup mecanic
                // Assume last char is pos identifier on known group string paterns
                if (preg_match('/([1-4]{2,3}|[\sa-d][1-4]|[\s1-4][a-d])$/i', $group)) {
                    $lChar = substr($group, -1);
                    if ((int) $lChar) {
                        $lChar = (int) $lChar - 1;
                    } else {
                        $lChar = ord(strtolower($lChar)) - 97; // 97 is a
                    }

                    // Make sure we don't go full ham
                    if ($lChar > $weightPos && $lChar < $div) {
                        $weightPos = $lChar;
                    }
                }

                $positions[$group] = $weightPos;
                ++$weightPos;
                // Reset pos if overflowing diviser
                // Can happen on full width blocks
                if ($weightPos % $div === 0) {
                    $weightPos = 0;
                }
            }
            $parentDiv = $div;
        }

        // Draw event blocks on image
        foreach ($blocks as $b) {
            $diviser = 0;
            $position = 0;

            // Addup divisers
            foreach ($b['groups'] as $g) {
                if (array_key_exists($g, $divisers)) {
                    $diviser += 1 / $divisers[$g];
                }
            }

            // Invert divisers after addition
            $diviser = $diviser ? 1 / $diviser : 1;

            // Get position from first group and adjust based on group count
            if (count($b['groups'])) {
                $defaultGroup = $b['groups'][0];
                if (array_key_exists($defaultGroup, $positions)) {
                    $position = $positions[$defaultGroup];
                }
                if (count($b['groups']) > 1) {
                    $position /= count($b['groups']);
                }
            }

            // Real draw
            $this->drawEvent(
                $b['day'],
                $b['startHour'],
                $b['duration'],
                $b['text'],
                $b['bgColor'],
                $b['borderColor'],
                $b['textColor'],
                $diviser,
                $position
            );
        }

        // Save .png
        imagepng($this->img, Media::getRealPath().$filename, -1, PNG_NO_FILTER);
        imagedestroy($this->img);
    }

    /**
     * Creates calendar base structure (hours & days lines).
     */
    private function initCalendar()
    {
        $this->img = imagecreate($this->opts['width'], $this->opts['height']);

        $this->color['white'] = imagecolorallocate($this->img, 255, 255, 255);
        $this->color['black'] = imagecolorallocate($this->img, 0, 0, 0);
        $this->color['grey'] = imagecolorallocate($this->img, 150, 150, 150);
        $this->color['lightgrey'] = imagecolorallocate($this->img, 220, 220, 220);
        $this->color['red'] = imagecolorallocate($this->img, 255, 0, 0);
        $this->color['green'] = imagecolorallocate($this->img, 0, 255, 0);
        $this->color['blue'] = imagecolorallocate($this->img, 0, 0, 255);
        $this->color['orange'] = imagecolorallocate($this->img, 150, 20, 20);

        $this->strW = imagefontwidth($this->font);
        $this->strH = imagefontheight($this->font);

        // Header margin
        $this->headerHeight = 4 + $this->strH + 3;
        // Left block margin
        $this->leftBlockWidth = 2 + $this->strW * 2 + 2;

        // Draw hours
        $hoursCount = count(self::HOURS);
        $this->hourStep = ($this->opts['height'] - $this->headerHeight) / $hoursCount;
        for ($i = 0; $i < $hoursCount; ++$i) {
            $offset = $this->headerHeight + $this->hourStep * $i;
            imageline($this->img, 0, $offset, $this->opts['width'] - 1, $offset, $this->color['grey']);

            imagesetstyle($this->img, [$this->color['lightgrey'], $this->color['white'], $this->color['white'], $this->color['white']]);

            imageline($this->img, $this->leftBlockWidth / 1.2, $offset + $this->hourStep / 4, $this->opts['width'] - 1, $offset + $this->hourStep / 4, IMG_COLOR_STYLED);

            imageline($this->img, $this->leftBlockWidth / 1.2, $offset + $this->hourStep / 1.3333, $this->opts['width'] - 1, $offset + $this->hourStep / 1.3333, IMG_COLOR_STYLED);

            imagesetstyle($this->img, [$this->color['grey'], $this->color['white'], $this->color['white']]);

            imageline($this->img, $this->leftBlockWidth / 1.6, $offset + $this->hourStep / 2, $this->opts['width'] - 1, $offset + $this->hourStep / 2, IMG_COLOR_STYLED);

            imagestring(
                $this->img,
                $this->font,
                2,
                $offset + 4,
                self::HOURS[$i],
                $this->color['black']
            );
        }

        // Draw days
        $daysCount = count(self::DAYS);
        $this->dayStep = ($this->opts['width'] - $this->leftBlockWidth) / $daysCount;
        for ($i = 0; $i < $daysCount; ++$i) {
            $offset = $this->leftBlockWidth + $this->dayStep * $i;
            imagestring(
                $this->img,
                $this->font,
                $offset + ($this->dayStep / 2) - ($this->strW * strlen(\Yii::t('app', self::DAYS[$i])) / 2),
                4,
                \Yii::t('app', self::DAYS[$i]),
                $this->color['black']
            );

            imageline($this->img, $offset, 0, $offset, $this->opts['height'] - 1, $this->color['black']);
        }
    }

    /**
     * Draws an event block on calendar image.
     *
     * @param int      $day         day index
     * @param int      $hour        hour index
     * @param int      $duration    duration in hours
     * @param string[] $text        event content text
     * @param string   $bgColor     background color
     * @param string   $borderColor border color
     * @param string   $textColor   text color
     * @param int      $diviser     event width diviser
     * @param int      $position    position inside day column
     */
    private function drawEvent($day = 0, $hour = 0, $duration = 1, $text = [], $bgColor = 'white', $borderColor = 'black', $textColor = 'black', $diviser = 1, $position = 0)
    {
        // Setup x, y, width and height
        $x1 = $this->leftBlockWidth + $this->dayStep * $day + ($this->dayStep / $diviser * $position);
        $x2 = $x1 + ($this->dayStep / $diviser);
        $y1 = $this->headerHeight + $this->hourStep * $hour;
        $y2 = $y1 + $this->hourStep * $duration;
        // Fill rectangle with border color
        imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $this->color[$borderColor]);
        // Then fill 1px smaller rectangle with background color
        imagefilledrectangle($this->img, $x1 + 1, $y1 + 1, $x2 - 1, $y2 - 1, $this->color[$bgColor]);

        // Prepare text content for imagestring()
        $text = self::trimArray($text);

        // Setup text position
        $xCenter = ($x1 + ($x2 - $x1) / 2);
        $yCenter = ($y1 + ($y2 - $y1) / 2);
        $availChars = floor(($x2 - $x1) / $this->strW);
        $availLines = floor(($y2 - $y1) / $this->strH);
        $overLines = $availLines - count($text);

        // Remove text going over block height
        while (count($text) > $availLines) {
            array_pop($text);
        }

        $dispText = [];
        foreach ($text as $i => $t) {
            // Try to cut too long lines
            if (strlen($t) > $availChars) {
                $dispText[] = trim(substr($t, 0, $availChars));
                // Wrap the rest on line bellow if possible
                while ($overLines > 0) {
                    --$overLines;
                    $t = substr($t, $availChars);
                    if (strlen($t) > 1) { // Cut single char alone on a line
                        $dispText[] = trim(substr($t, 0, $availChars));
                    } else {
                        break;
                    }
                }
            } else {
                $dispText[] = trim($t);
            }
            // Add a blank line under title if possible
            if ($i === 0 && $overLines > 0) {
                $dispText[] = '';
            }
        }

        // Draw text
        foreach ($dispText as $i => $t) {
            imagestring(
                $this->img,
                $this->font,
                $xCenter - (strlen($t) * $this->strW) / 2,
                $yCenter - (count($dispText) * $this->strH) / 2 + ($this->strH * $i),
                $t,
                $this->color[$textColor]
            );
        }
    }

    /**
     * Checks for event overlap.
     *
     * @param array $a first event
     * @param array $b second event
     *
     * @return bool overlaps
     */
    private static function overlap($a, $b)
    {
        return $a['day'] == $b['day'] && $a != $b && (
            ($a['startHour'] >= $b['startHour'] && $a['startHour'] < $b['endHour']) ||
            ($a['endHour'] > $b['startHour'] && $a['endHour'] <= $b['endHour'])
            );
    }

    /**
     * Filter string from feed.
     *
     * @param string $str  input string
     * @param string $type string type
     *
     * @return string filtered string
     */
    private static function filter($str, $type = 'desc')
    {
        $str = preg_replace([
            '/\s{2,}/',
            '/\s*\\\,\s*/',
            '/ï/',
            '/è/',
            '/\s*\([^\)]*\)/',
        ], [
            ' ',
            ', ',
            'i',
            'e',
            '',
        ], trim($str));
        switch ($type) {
            case 'name':
                return preg_replace([
                    '/^\d+\s*-/',
                    '/^\d+\s+/',
                ], [
                    '',
                    '',
                ], $str);
            case 'location':
                return preg_replace([
                    '/(\d) (\d{3})/',
                ], [
                    '\\1-\\2',
                ], trim(explode(',', $str)[0]));
            case 'desc':
                return preg_replace([
                    '/(modif).*/',
                ], [
                    '',
                ], $str);
            default:
                return $str;
        }
    }

    /**
     * Trim and decode array content.
     *
     * @param string[] $arr input array
     *
     * @return string[] trimed array
     */
    private static function trimArray($arr)
    {
        $res = [];

        if (!is_array($arr)) {
            $arr = [$arr];
        }

        foreach ($arr as $text) {
            $decodedText = utf8_decode(trim($text));
            if (!$decodedText) {
                continue;
            }
            $res[] = $decodedText;
        }

        return $res;
    }
}
