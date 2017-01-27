<?php

namespace app\models\types;

use Yii;
use ICal\ICal;
use app\models\ContentType;
use yii\helpers\Url;

/**
 * This is the model class for Agenda content type.
 */
class Agenda extends ContentType
{
    const BASE_CACHE_TIME = 7200; // 2 hours
    const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    const HOUR_MIN = 8;
    const HOUR_MAX = 18;
    //const HOURS = [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18];

    public $html = '<div class="agenda">%data%</div>';
    public $css = <<<EO1
%field% .agenda { width: 100%; height: 100%; text-align: center; background-color: white; }
%field% .agenda-header { }
%field% .agenda-contents { width: 100%; height: calc(100% - 1.3em); display: table; table-layout: fixed; border-collapse: collapse; }
%field% .agenda-time-wrapper { display: table-cell; width: 2.2em; }
%field% .agenda-time { position: relative; top: 1.5em; /*height: calc(100% - 3em);*/}
%field% .agenda-time-h { position: absolute; border-top: solid 1px black; width: 100%; }
%field% .agenda-time-m { position: absolute; border-top: dotted 1px black; right: 0; }
%field% .agenda-day { display: table-cell; }
%field% .agenda-day-header { border-style: solid; border-color: red; border-width: 1px 0 1px 1px; }
%field% .agenda-day:last-child .agenda-day-header { border-width: 1px 1px 1px 1px; }
%field% .agenda-day-contents { border-style: solid; border-color: green; border-width: 1px 0 1px 1px; width: 100%; height: calc(100% - 1.3em); display: table; position: relative; }
%field% .agenda-day:last-child .agenda-day-contents { border-width: 1px 1px 1px 1px; }
%field% .agenda-event { position: absolute; overflow: hidden; border-style: solid; border-color: blue; border-width: 1px 0 1px 0; }
EO1;
    public $input = 'url';
    public $output = 'raw';
    public $usable = true;
    public $preview = '@web/images/agenda.preview.jpg';

    private static $translit;
    private $color = [];
    private $opts;

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
            if ($agenda) {
                //self::toCache($data, $agenda);
            }
        }

        return $agenda;
    }

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

        $tz = new \DateTimeZone(ini_get('date.timezone'));
        self::$translit = \Transliterator::create('Latin-ASCII');
        if (!self::$translit) {
            return null;
        }

        $format = [
            'minHour' => self::HOUR_MIN,
            'maxHour' => self::HOUR_MAX,
        ];

        $parsedEvents = [];

        foreach ($events as $e) {
            // Convert timezones
            $start = (new \DateTime($e->dtstart))->setTimeZone($tz);
            $end = (new \DateTime($e->dtend))->setTimeZone($tz);

            // Event info
            $b = [
                'dow' => $start->format('w') - 1,
                'start' => $start->format('G') + ($start->format('i') / 60.0),
                'startStr' => $start->format('G:i'),
                'end' => $end->format('G') + ($end->format('i') / 60.0),
                'endStr' => $end->format('G:i'),
                'name' =>self::filter($e->summary, 'name'),
                'locations' => self::arrayFilter(explode(',', $e->location), 'location'),
                'desc' => self::arrayFilter(explode(PHP_EOL, $e->description), 'description'),
            ];
            $b['duration'] = $b['end'] - $b['start'];

            if ($b['start'] < $format['minHour']) {
                $format['minHour'] = $b['start'];
            }
            if ($b['end'] > $format['maxHour']) {
                $format['maxHour'] = $b['end'];
            }

            if (!array_key_exists($b['dow'], $parsedEvents)) {
                $parsedEvents[$b['dow']] = [];
            }

            $parsedEvents[$b['dow']][] = $b;
        }

        $format['dayLen'] = $format['maxHour'] - $format['minHour'];

        return ['info' => $format, 'events' => $parsedEvents];
    }

    public function blockize($agenda)
    {
        $scanOffset = 0.1;

        $blocks = [];

        foreach ($agenda['events'] as $day => $events) {
            usort($events, function ($a, $b) {
                //return $a['desc'][0] - $b['desc'][0];
                return strcmp($a['desc'][0], $b['desc'][0]);
            });

            for ($i = $agenda['info']['minHour']; $i <= $agenda['info']['maxHour']; $i += $scanOffset) {
                $overlap = [];
                foreach ($events as $k => $e) {
                    if ($e['start'] < $i && $i < $e['end']) {
                        $overlap[] = $k;
                    }
                }

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
                            $e['overlap'] = array_unique(array_merge($e['overlap'], $overlap));
                        }

                        $events[$k] = $e;
                    }
                }
            }

            foreach ($events as $k => $e) {
                if ($e['overlaps'] < 2) {
                    $e['position'] = 0;
                    $events[$k] = $e;
                    continue;
                }

                if (array_key_exists('position', $e)) {
                    continue;
                }

                $spots = range(0, $e['overlaps'] - 1);
                for ($i = 0; $i < count($e['overlap']); $i++) {
                    $overlaped = $events[$e['overlap'][$i]];
                    if (array_key_exists('position', $overlaped)) {
                        unset($spots[$overlaped['position']]);
                    }
                }

                $e['spots'] = $spots;

                $e['position'] = array_shift($spots);

                $events[$k] = $e;
            }

            $blocks[$day] = $events;
        }

        return $blocks;
    }

    private static function locations($agenda)
    {
        $locations = [];
        foreach ($agenda['events'] as $events) {
            foreach ($events as $e) {
                if (!array_key_exists('locations', $e)) {
                    continue;
                }

                foreach ($e['locations'] as $l) {
                    if (!array_key_exists($l, $locations)) {
                        $locations[$l] = 0;
                    }
                    $locations[$l]++;
                }
            }
        }

        arsort($locations);

        return $locations;
    }

    private static function descriptions($agenda)
    {
        $descriptions = [];
        foreach ($agenda['events'] as $events) {
            foreach ($events as $e) {
                if (!array_key_exists('desc', $e)) {
                    continue;
                }

                foreach ($e['desc'] as $d) {
                    if (!array_key_exists($d, $descriptions)) {
                        $descriptions[$d] = 0;
                    }
                    $descriptions[$d] += 1/($e['overlaps']*2);
                    break;
                }
            }
        }

        arsort($descriptions);

        return $descriptions;
    }

    private static function hasOverlaps($agenda)
    {
        foreach ($agenda['events'] as $events) {
            foreach ($events as $e) {
                if ($e['overlaps'] > 1) {
                    return true;
                }
            }
        }
        return false;
    }

    private static function genTitle($agenda)
    {
        if (!self::hasOverlaps($agenda)) {
            $locations = self::locations($agenda);
            if ($locations < 3) {
                reset($locations);
                return key($locations);
            }
        }

        $descriptions = self::descriptions($agenda);
        reset($descriptions);
        return key($descriptions);
    }

    public function finalize($agenda)
    {
        $info = $agenda['info'];

        $info['title'] = self::genTitle($agenda);

        return $info;
    }

    public function render($agenda)
    {
        $h = '<div class="agenda-header">'.$agenda['info']['title'].'</div><div class="agenda-contents">';

        $timeTraces = 0.25;
        $h .= '<div class="agenda-time-wrapper"><div class="agenda-time">';
        for ($i = ceil($agenda['info']['minHour']); $i < floor($agenda['info']['maxHour']); $i += $timeTraces) {
            if (fmod($i, 1) == 0) {
                $h .= '<div class="agenda-time-h" style="top: '.((($i - $agenda['info']['minHour']) / $agenda['info']['dayLen']) * 100).'%;">'.$i.'h</div>';
            } else {
                $h .= '<div class="agenda-time-m" style="top: '.((($i - $agenda['info']['minHour']) / $agenda['info']['dayLen']) * 100).'%; width: '.(fmod($i, 0.5) == 0 ? 40 : 20).'%;"></div>';
            }
        }
        $h .= '</div></div>';

        foreach ($agenda['events'] as $day => $events) {
            $h .= '<div class="agenda-day" id="day-'.$day.'">'.
                '<div class="agenda-day-header">'.\Yii::t('app', self::DAYS[$day]).'</div>'.
                '<div class="agenda-day-contents">';

            foreach ($events as $e) {
                $style = [
                    'top' => ((($e['start'] - $agenda['info']['minHour']) / $agenda['info']['dayLen']) * 100).'%',
                    'bottom' => ((($agenda['info']['maxHour'] - $e['end']) / $agenda['info']['dayLen']) * 100).'%',
                    'left' => ($e['position'] / $e['overlaps'] * 100).'%',
                    'right' => ((1 - ($e['position'] + 1) / $e['overlaps']) * 100).'%',
                    'background-color' => $this->getColor($e['desc'][0]),
                ];
                $styleStr = implode('; ', array_map(function ($k, $v) {
                    return $k.':'.$v;
                }, array_keys($style), $style));

                $content = [
                    //$e['overlaps'],
                    //$e['position'],
                    $e['name'],
                    implode(',', $e['locations']),
                    $e['desc'][0],
                    //$e['startStr'].' - '.$e['endStr'],
                ];

                $h .= '<div class="agenda-event" style="'.$styleStr.'">'.implode('<br />', $content).'</div>';
            }

            $h .= '</div></div>';
        }

        $h .= '</div>';

        return $h;
    }

    public function genAgenda($url)
    {
        $this->opts = \Yii::$app->params['agenda'];

        $content = self::downloadContent($url);

        $agenda = $this->parseIcal($content);
        if (!$agenda) {
            return null;
        }

        $agenda['events'] = $this->blockize($agenda);

        $agenda['info'] = $this->finalize($agenda);

        return $this->render($agenda);
    }

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
                    '/(\d) (\d{3})/',
                ], [
                    '\\1-\\2',
                ], trim(explode(',', $str)[0]));
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

    private function getColor($str)
    {
        if (array_key_exists($str, $this->color)) {
            return $this->color[$str];
        }

        // %160 + 95 make colors brighter
        $hash = md5($str);
        $this->color[$str] = sprintf(
            '#%X%X%X',
            hexdec(substr($hash, 0, 2)) % 140 + 115,
            hexdec(substr($hash, 2, 2)) % 140 + 115,
            hexdec(substr($hash, 4, 2)) % 140 + 115
        );

        return $this->color[$str];
    }
}
