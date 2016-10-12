<?php

namespace app\models\types;

use ICal\ICal;
use yii\helpers\Url;

/**
 * This is the model class for content uploads.
 */
class Agenda extends RSS
{
    const BASE_CACHE_TIME = 7200; // 1 hour
    const WIDTH = 1260;
    const HEIGHT = 880;
    const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    const HOURS = [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18];
    const CALENDAR_TZ = 'UTC';
    //const DISPLAY_TZ = ini_get('date.timezone');

    public static $typeName = 'Agenda';
    public static $typeDescription = 'Display an agenda from an RSS feed.';
    public static $html = '<img class="agenda" src="%data%" />';
    public static $css = '%field% { text-align: center; vertical-align: middle; } %field% img { height: 100%; width: 100%; object-fit: contain; }';
    public static $input = 'url';
    public static $output = 'url';
    public static $usable = true;

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

    protected static function downloadFeed($url)
    {
        if (\Yii::$app->params['proxy']) {
            $ctx = [
                'http' => [
                    'proxy' => 'tcp://vdebian:8080',
                    'request_fulluri' => true,
                ],
            ];

            return file_get_contents(urldecode($url), false, stream_context_create($ctx));
        } else {
            return file_get_contents(urldecode($url));
        }
    }

    public function processData($data)
    {
        return Url::to(['frontend/get', 'typeId' => self::$typeName, 'data' => urlencode($data)]);
    }

    public function get($data)
    {
        $cache = \Yii::$app->cache;
        $cacheKey = self::$typeName.$data;
        if ($cache->exists($cacheKey)) {
            $content = $cache->get($cacheKey);
        } else {
            $content = self::downloadFeed($data);
            $cache->set($cacheKey, $content, self::BASE_CACHE_TIME);
        }

        $ical = new ICal();
        $ical->initString($content);

        $events = $ical->eventsFromRange(self::DAYS[0].' this week', self::DAYS[count(self::DAYS) - 1].' this week 23:59');
        if (!is_array($events) || !count($events)) {
            return;
        }

        $this->initCalendar();

        $utcTz = new \DateTimeZone(self::CALENDAR_TZ);
        $localTz = new \DateTimeZone(ini_get('date.timezone'));
        $firstDay = new \DateTime(self::DAYS[0]);

        $blocks = [];
        // Build blocks
        foreach ($events as $e) {
            $start = new \DateTime('@'.$e->dtstart_array[2], $utcTz);
            $start->setTimeZone($localTz);
            $end = new \DateTime('@'.$e->dtend_array[2], $utcTz);
            $end->setTimeZone($localTz);

            $dow = $start->format('w') - 1;
            $startHour = $start->format('G') + ($start->format('i') / 60.0) - self::HOURS[0];
            $endHour = $end->format('G') + ($end->format('i') / 60.0) - self::HOURS[0];
            $duration = $endHour - $startHour;
            $name = self::filter(html_entity_decode($e->summary, ENT_QUOTES), 'name');
            $location = self::filter(html_entity_decode($e->location, ENT_QUOTES), 'location');
            $desc = array_values(array_filter(array_map('self::filter', explode('\n', html_entity_decode($e->description, ENT_QUOTES)))));
            $teacher = array_pop($desc);
            if (strlen($teacher) < 8 && strpos($teacher, '.') === false) {
                $desc[] = $teacher;
                $teacher = null;
            }
            sort($desc);
            $group = implode(', ', $desc);

            $text = [
                $name,
                $location,
                $group,
                $teacher,
            ];

            if (!array_key_exists($group, $this->color)) {
                $groupHash = md5($group);
                $this->color[$group] = imagecolorallocate(
                    $this->img,
                    hexdec(substr($groupHash, 0, 2)) % 160 + 95,
                    hexdec(substr($groupHash, 2, 2)) % 160 + 95,
                    hexdec(substr($groupHash, 4, 2)) % 160 + 95
                );
            }

            // Init event block
            $blocks[] = [
                'uid' => $e->uid,
                'day' => $dow,
                'startHour' => $startHour,
                'endHour' => $endHour,
                'duration' => $duration,
                'text' => $text,
                'group' => $group,
                'groups' => $desc,
                'bgColor' => $group,
                'borderColor' => 'black',
                'textColor' => 'black',
                'diviser' => 1,
                'position' => 0,
            ];
        }

        // exit(var_dump($blocks));

        // Sort blocks by day and group
        usort($blocks, function ($a, $b) {
            if ($a['day'] != $b['day']) {
                return $a['day'] - $b['day'];
            }

            return $a['group'] > $b['group'] ? 1 : -1;
        });

        // Lookup overlap by group and assign them weights used for block width division
        $diviserWeight = [];
        foreach ($blocks as $b) {
            foreach ($b['groups'] as $g) {
                $w = 1;
                if (preg_match('/\s[a-d]\ss?\d-s?\d$/i', $g)) {
                    $w = 2;//strlen($g) * 4;
                } elseif (preg_match('/s?\d-s?\d$/i', $g)) {
                    $w = 1;//strlen($g);
                } elseif (preg_match('/[\s\d][a-d]$/i', $g)) {
                    $w = 2;//strlen($g) * 4;
                } elseif (preg_match('/\d$/i', $g)) {
                    $w = 4;//strlen($g) * 8;
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
        // exit(var_dump($diviserWeight));

        $divisers = [];
        $positions = [];
        foreach ($diviserWeight as $weight => $groups) {
            $weightPos = 0;
            foreach ($groups as $group) {
                $divisers[$group] = $weight;
                $positions[$weight][$group] = $weightPos;
                ++$weightPos;
                if ($weightPos % $weight === 0) {
                    $weightPos = 0;
                }
            }
        }
        // exit(var_dump($divisers, $positions));

        // Draw event blocks
        foreach ($blocks as $b) {
            $baseDiviser = null;
            $diviser = 0;
            $position = 0;
            foreach ($b['groups'] as $g) {
                if (array_key_exists($g, $divisers)) {
                    $diviser += 1 / $divisers[$g];
                    if (!$baseDiviser) {
                        $baseDiviser = $divisers[$g];
                    }
                }
            }
            if (!$baseDiviser) {
                $baseDiviser = 1;
            }
            if (array_key_exists($baseDiviser, $positions) && array_key_exists($b['groups'][0], $positions[$baseDiviser])) {
                $position = $positions[$baseDiviser][$b['groups'][0]];
            }
            if (!$diviser) {
                $diviser = 1;
            } else {
                $diviser = 1 / $diviser;
            }
            if ($baseDiviser != $diviser) {
                $position *= $diviser / $baseDiviser;
            }
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

        // Send png
        \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        header('Content-type: image/png');
        imagepng($this->img);
        imagedestroy($this->img);
    }

    private function initCalendar()
    {
        $this->img = imagecreate(self::WIDTH, self::HEIGHT);

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
        $this->hourStep = (self::HEIGHT - $this->headerHeight) / count(self::HOURS);
        for ($i = 0; $i < count(self::HOURS); ++$i) {
            $offset = $this->headerHeight + $this->hourStep * $i;
            imageline($this->img, 0, $offset, self::WIDTH - 1, $offset, $this->color['grey']);

            imagesetstyle($this->img, [$this->color['lightgrey'], $this->color['white'], $this->color['white'], $this->color['white']]);

            imageline($this->img, $this->leftBlockWidth / 1.2, $offset + $this->hourStep / 4, self::WIDTH - 1, $offset + $this->hourStep / 4, IMG_COLOR_STYLED);

            imageline($this->img, $this->leftBlockWidth / 1.2, $offset + $this->hourStep / 1.3333, self::WIDTH - 1, $offset + $this->hourStep / 1.3333, IMG_COLOR_STYLED);

            imagesetstyle($this->img, [$this->color['grey'], $this->color['white'], $this->color['white']]);

            imageline($this->img, $this->leftBlockWidth / 1.6, $offset + $this->hourStep / 2, self::WIDTH - 1, $offset + $this->hourStep / 2, IMG_COLOR_STYLED);

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
        $this->dayStep = (self::WIDTH - $this->leftBlockWidth) / count(self::DAYS);
        for ($i = 0; $i < count(self::DAYS); ++$i) {
            $offset = $this->leftBlockWidth + $this->dayStep * $i;
            imagestring(
                $this->img,
                $this->font,
                $offset + ($this->dayStep / 2) - ($this->strW * strlen(\Yii::t('app', self::DAYS[$i])) / 2),
                4,
                \Yii::t('app', self::DAYS[$i]),
                $this->color['black']
            );

            imageline($this->img, $offset, 0, $offset, self::HEIGHT - 1, $this->color['black']);
        }
    }

    private function drawEvent($day = 0, $hour = 0, $duration = 1, $text = 'Derp', $bgColor = 'white', $borderColor = 'black', $textColor = 'black', $diviser = 1, $position = 0)
    {
        $x1 = $this->leftBlockWidth + $this->dayStep * $day + ($this->dayStep / $diviser * $position);
        $x2 = $x1 + ($this->dayStep / $diviser);
        $y1 = $this->headerHeight + $this->hourStep * $hour;
        $y2 = $y1 + $this->hourStep * $duration;
        imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $this->color[$borderColor]);
        imagefilledrectangle($this->img, $x1 + 1, $y1 + 1, $x2 - 1, $y2 - 1, $this->color[$bgColor]);

        $text = self::trimArray($text);

        $xCenter = ($x1 + ($x2 - $x1) / 2);
        $yCenter = ($y1 + ($y2 - $y1) / 2);
        $availChars = floor(($x2 - $x1) / $this->strW);
        $availLines = floor(($y2 - $y1) / $this->strH);
        $overLines = $availLines - count($text);
        while (count($text) > $availLines) {
            array_pop($text);
        }

        $dispText = [];
        foreach ($text as $i => $t) {
            if (strlen($t) > $availChars) {
                $dispText[] = trim(substr($t, 0, $availChars));
                while ($i === 0 && $overLines > 0) {
                    --$overLines;
                    $t = substr($t, $availChars);
                    if (strlen($t) > 1) {
                        $dispText[] = trim(substr($t, 0, $availChars));
                    } else {
                        break;
                    }
                }
            } else {
                $dispText[] = trim($t);
            }
            if ($i === 0 && $overLines > 0) {
                $dispText[] = '';
            }
        }

        foreach ($dispText as $i => $t) {
            imagestring(
                $this->img,
                $this->font,
                $xCenter - (strlen($t) * $this->strW) / 2,
                $yCenter - (count($dispText) * $this->strH) / 2 + ($this->strH * $i),
                $t,
                $this->color[$textColor]
            );

            // imagettftext(
            //     $this->img,
            //     $this->fontSize,
            //     0,
            //     $xCenter - (strlen($t) * $this->strW) / 2,
            //     $yCenter - (count($dispText) * $this->strH) / 2 + ($this->strH * $i),
            //     $this->color[$textColor],
            //     $this->fontFile,
            //     $t
            // );
        }
    }

    private static function overlap($a, $b)
    {
        return $a['day'] == $b['day'] && $a != $b && (
            ($a['startHour'] >= $b['startHour'] && $a['startHour'] < $b['endHour']) ||
            ($a['endHour'] > $b['startHour'] && $a['endHour'] <= $b['endHour'])
            );
    }

    private static function filter($str, $type = 'desc')
    {
        $str = preg_replace([
            '/\s{2,}/',
            '/\s*\\\,\s*/',
            '/ï/',
            '/\s*\([^\)]*\)/',
        ], [
            ' ',
            ', ',
            'i',
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
                ], $str);
            case 'desc':
                return preg_replace([
                    '/.{22,}/',
                ], [
                    '',
                ], $str);
            default:
                return $str;
        }
    }

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
