<?php

namespace app\models\types;

use Yii;
use ICal\ICal;
use app\models\ContentType;

/**
 * This is the model class for Agenda content type.
 */
class Agenda extends ContentType
{
    const BASE_CACHE_TIME = 7200; // 2 hours
    const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    const HOUR_MIN = 8;
    const HOUR_MAX = 18;

    public $html = '<div class="agenda">%data%</div>';
    public $css = <<<'EO1'
%field% .agenda { width: 100%; height: 100%; text-align: center; background-color: white; color: black; }
%field% .agenda-header { font-weight: bold; }
%field% .agenda-contents { width: 100%; height: calc(100% - 1.3em); display: table; table-layout: fixed; border-collapse: collapse; }
%field% .agenda-time { display: table-cell; width: 2.2em;  border: solid 1px black; }
%field% .agenda-time-header { }
%field% .agenda-time-contents { width: 100%; height: calc(100% - 1.3em); display: table; position: relative; }
%field% .agenda-time-h { position: absolute; border-top: solid 1px black; width: 100%; }
%field% .agenda-time-m { position: absolute; border-top: dotted 1px black; right: 0; }
%field% .agenda-time-trace { position: absolute; border-top: dotted 1px gray; width: 100%; }
%field% .agenda-day { display: table-cell; border: solid 1px black; }
%field% .agenda-day-header { border-bottom: solid 1px black; }
%field% .agenda-day-contents { width: 100%; height: calc(100% - 1.3em); display: table; position: relative; }
%field% .agenda-event { position: absolute; overflow: hidden; border-bottom: solid 1px black; z-index: 10; }
%field% .agenda-event-desc { font-weight: bold; font-size: 1.1em; }
%field% .agenda-event-location { font-size: 1.1em; white-space: nowrap; }
%field% .agenda-event-name { word-break: break-all; display: block; }
EO1;
    public $input = 'url';
    public $output = 'raw';
    public $usable = true;
    public $exemple = '@web/images/agenda.preview.jpg';
    public $canPreview = true;

    private static $translit;
    private $color = [];
    private $opts;
    private $overlapScanOffset = 0.1;
    private $tz;

    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->name = Yii::t('app', 'Agenda');
        $this->description = Yii::t('app', 'Display an agenda from an ICal feed.');
    }

    /**
     * {@inheritdoc}
     */
    public function processData($data)
    {
        $agenda = self::fromCache($data);
        if (!$agenda) {
            $agenda = $this->genAgenda($data);
            if ($agenda !== null) {
                self::toCache($data, $agenda);
            }
        }

        return $agenda;
    }

    /**
     * Extract data from ical event.
     *
     * @param array $e ical event
     *
     * @return array parsed event
     */
    private function parseEvent($e)
    {
        // Convert timezones
        $start = (new \DateTime($e->dtstart))->setTimeZone($this->tz);
        $end = (new \DateTime($e->dtend))->setTimeZone($this->tz);

        // Event info
        $event = [
            'dow' => $start->format('w') - 1,
            'dayMonth' => $start->format('d/m'),
            'start' => $start->format('G') + ($start->format('i') / 60.0),
            'startStr' => $start->format('G:i'),
            'end' => $end->format('G') + ($end->format('i') / 60.0),
            'endStr' => $end->format('G:i'),
            'name' => self::filter($e->summary, 'name'),
            'locations' => self::arrayFilter(explode(',', $e->location), 'location'),
            'desc' => self::arrayFilter(explode(PHP_EOL, $e->description), 'description'),
        ];
        $event['duration'] = $event['end'] - $event['start'];

        return $event;
    }

    /**
     * Read .ical data and parse to day-based array.
     *
     * @param string $data ical raw data
     *
     * @return array agenda
     */
    public function parseIcal($data)
    {
        // Init ICal parser
        $ical = new ICal();
        $ical->initString($data);

        // Retrieve event for this week only
        $events = $ical->eventsFromRange(self::DAYS[0].' this week', self::DAYS[count(self::DAYS) - 1].' this week 23:59');

        if (!is_array($events) || !count($events)) {
            return null;
        }

        // Use own timezone to display
        $this->tz = new \DateTimeZone(ini_get('date.timezone'));
        // Always transliterate text contents
        self::$translit = \Transliterator::create('Latin-ASCII');
        if (!self::$translit) {
            return null;
        }

        // Base agenda format info
        $info = [
            'minHour' => self::HOUR_MIN,
            'maxHour' => self::HOUR_MAX,
            'days' => [],
        ];

        $parsedEvents = [];

        foreach ($events as $event) {
            $e = $this->parseEvent($event);

            // Adjust agenda format based on events
            if ($e['start'] < $info['minHour']) {
                $info['minHour'] = $e['start'];
            }
            if ($e['end'] > $info['maxHour']) {
                $info['maxHour'] = $e['end'];
            }

            // Only add days with events
            if (!array_key_exists($e['dow'], $parsedEvents)) {
                $parsedEvents[$e['dow']] = [];
                $info['days'][$e['dow']] = $e['dayMonth'];
            }

            $parsedEvents[$e['dow']][] = $e;
        }

        return ['info' => $info, 'events' => $parsedEvents];
    }

    /**
     * Loop through day events and tag with overlaps.
     *
     * @param array $events
     * @param int   $from   start hour
     * @param int   $to     end hour
     *
     * @return array tagged events
     */
    private function tagOverlaps($events, $from, $to)
    {
        // Scan each 0.1h for overlapping events
        for ($i = $from; $i <= $to; $i += $this->overlapScanOffset) {
            // $overlap is every overlapping event
            $overlap = $this->scanOverlap($events, $i);

            // $overlaps is maximum concurrent overlappings
            // Used to fix block width
            $overlaps = count($overlap);

            foreach ($events as $k => $e) {
                if ($e['start'] < $i && $i < $e['end']) {
                    if (!array_key_exists('overlaps', $e)) {
                        $e['overlaps'] = $overlaps;
                        $e['overlap'] = $overlap;
                    } else {
                        if ($overlaps >= $e['overlaps']) {
                            $e['overlaps'] = $overlaps;
                        }
                        // Merge overlap to always get full range of overlapping events
                        // Used to calculate block position
                        $e['overlap'] = array_unique(array_merge($e['overlap'], $overlap));
                    }

                    $events[$k] = $e;
                }
            }
        }

        return $events;
    }

    /**
     * Scan for overlap at precise time.
     *
     * @param array $events
     * @param int   $at     scan hour
     *
     * @return array overlap
     */
    private function scanOverlap($events, $at)
    {
        $overlap = [];
        foreach ($events as $k => $e) {
            if ($e['start'] < $at && $at < $e['end']) {
                $overlap[] = $k;
            }
        }

        return $overlap;
    }

    /**
     * Loop through day events and scan for open position.
     *
     * @param array $events
     *
     * @return array positionned blocks
     */
    private function positionBlocks($events)
    {
        foreach ($events as $k => $e) {
            if ($e['overlaps'] < 2) {
                // No overlap, easy mode
                $e['position'] = 0;
                $events[$k] = $e;
                continue;
            }

            if (array_key_exists('position', $e)) {
                // Position already set, don't touch
                continue;
            }

            // Find available spots for this event
            $spots = range(0, $e['overlaps'] - 1);
            $overlapCount = count($e['overlap']);
            for ($i = 0; $i < $overlapCount; ++$i) {
                $overlaped = $events[$e['overlap'][$i]];
                if (array_key_exists('position', $overlaped)) {
                    unset($spots[$overlaped['position']]);
                }
            }

            // Take first one
            $e['position'] = array_shift($spots);

            $events[$k] = $e;
        }

        return $events;
    }

    /**
     * Use agenda events data to build blocks for rendering.
     *
     * @param array $agenda
     *
     * @return array blocks
     */
    public function blockize($agenda)
    {
        $blocks = [];

        foreach ($agenda['events'] as $day => $events) {
            // Sort by desc first line
            usort($events, function ($a, $b) {
                return strcmp($a['desc'][0], $b['desc'][0]);
            });

            $events = $this->tagOverlaps($events, $agenda['info']['minHour'], $agenda['info']['maxHour']);

            $blocks[$day] = $this->positionBlocks($events);
        }

        return $blocks;
    }

    /**
     * Render agenda left column with hours.
     *
     * @param array $agenda
     *
     * @return string HTML column
     */
    private function renderHoursColumn($agenda)
    {
        $hourColumnIntervals = 0.25;
        $min = $agenda['info']['minHour'];
        $max = $agenda['info']['maxHour'];
        $len = $max - $min;

        $h = '<div class="agenda-time"><div class="agenda-time-header">&nbsp;</div><div class="agenda-time-contents">';
        for ($i = floor($min); $i < ceil($max); $i += $hourColumnIntervals) {
            if (fmod($i, 1) == 0) {
                $h .= '<div class="agenda-time-h" style="top: '.((($i - $min) / $len) * 100).'%;">'.$i.'h</div>';
            } else {
                $width = fmod($i, 0.5) == 0 ? 40 : 20;
                $h .= '<div class="agenda-time-m" style="top: '.((($i - $min) / $len) * 100).'%; width: '.$width.'%;"></div>';
            }
        }
        $h .= '</div></div>';

        return $h;
    }

    /**
     * Render agenda events blocks columns.
     *
     * @param array $agenda
     *
     * @return string HTML blocks columns
     */
    private function renderEvents($agenda)
    {
        $hourIntervals = 1;
        $min = $agenda['info']['minHour'];
        $max = $agenda['info']['maxHour'];
        $len = $max - $min;

        $h = '';
        foreach ($agenda['events'] as $day => $events) {
            // Draw day header
            $h .= '<div class="agenda-day" id="day-'.$day.'">'.
                '<div class="agenda-day-header">'.\Yii::t('app', self::DAYS[$day]).' '.$agenda['info']['days'][$day].'</div>'.
                '<div class="agenda-day-contents">';

            // Draw events
            foreach ($events as $e) {
                $style = [
                    'top' => ((($e['start'] - $min) / $len) * 100).'%',
                    'bottom' => ((($max - $e['end']) / $len) * 100).'%',
                    'left' => ($e['position'] / $e['overlaps'] * 100).'%',
                    'right' => ((1 - ($e['position'] + 1) / $e['overlaps']) * 100).'%',
                    'background-color' => $this->getColor($e['desc'][0]),
                ];
                $styleStr = implode('; ', array_map(function ($k, $v) {
                    return $k.':'.$v;
                }, array_keys($style), $style));

                $content = [];
                if (count($e['desc']) && $e['desc'][0]) {
                    $content[] = '<span class="agenda-event-desc">'.$e['desc'][0].'</span>';
                }
                foreach ($e['locations'] as $l) {
                    $content[] = ' <span class="agenda-event-location">'.$l.'</span>';
                }
                if ($e['name']) {
                    $content[] = '<span class="agenda-event-name">'.$e['name'].'</span>';
                }
                if ($e['startStr'] && $e['endStr']) {
                    $content[] = '<br />'.$e['startStr'].' - '.$e['endStr'];
                }

                $h .= '<div class="agenda-event" style="'.$styleStr.'">'.implode('', $content).'</div>';
            }

            // Draw background hour traces
            for ($i = floor($min) + 1; $i < ceil($max); $i += $hourIntervals) {
                $h .= '<div class="agenda-time-trace" style="top: '.((($i - $min) / $len) * 100).'%;"></div>';
            }

            $h .= '</div></div>';
        }

        return $h;
    }

    /**
     * Render agenda to HTML.
     *
     * @param array $agenda
     *
     * @return string HTML result
     */
    public function render($agenda)
    {
        $h = '<div class="agenda-header">%name%</div><div class="agenda-contents">';

        $h .= $this->renderHoursColumn($agenda);

        $h .= $this->renderEvents($agenda);

        $h .= '</div>';

        return $h;
    }

    /**
     * Generate agenda HTML from .ical url.
     *
     * @param string $url ical url
     *
     * @return string|null HTML agenda
     */
    public function genAgenda($url)
    {
        if (!$url) {
            return null;
        }
        $this->opts = \Yii::$app->params['agenda'];

        $content = self::downloadContent($url);
        if (!$content) {
            return null;
        }

        $agenda = $this->parseIcal($content);
        if (!$agenda) {
            return null;
        }

        $agenda['events'] = $this->blockize($agenda);

        return $this->render($agenda);
    }

    /**
     * Apply self::filter() to each array member.
     *
     * @param array  $arr  input
     * @param string $type array type
     *
     * @return array filtered output
     */
    private static function arrayFilter(array $arr, $type)
    {
        $res = [];
        foreach ($arr as $v) {
            $res[] = self::filter($v, $type);
        }

        return array_values(array_filter($res));
    }

    /**
     * Filter string from feed.
     *
     * @param string $str  input string
     * @param string $type string type
     *
     * @return string filtered string
     */
    private static function filter($str, $type)
    {
        $str = html_entity_decode($str);

        if (self::$translit) {
            $str = self::$translit->transliterate($str);
        }

        $str = preg_replace([
            '/\s{2,}/',
            '/\s*\\\,\s*/',
            '/\s*\([^\)]*\)/',
        ], [
            ' ',
            ', ',
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
                    '/(\d) (\d{3}).*/',
                ], [
                    '\\1-\\2',
                ], $str);
            case 'description':
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
     * Generate color based on string
     * Using MD5 to always get the same color for a given string.
     *
     * @param string $str
     *
     * @return string color hexcode
     */
    private function getColor($str)
    {
        if (array_key_exists($str, $this->color)) {
            return $this->color[$str];
        }

        // %140 + 95 make colors brighter
        $hash = md5($str);
        $this->color[$str] = sprintf(
            '#%X%X%X',
            hexdec(substr($hash, 0, 2)) % 140 + 95,
            hexdec(substr($hash, 2, 2)) % 140 + 95,
            hexdec(substr($hash, 4, 2)) % 140 + 95
        );

        return $this->color[$str];
    }
}
