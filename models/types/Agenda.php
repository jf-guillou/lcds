<?php

namespace app\models\types;

use ICal\ICal;

/**
 * This is the model class for content uploads.
 */
class Agenda extends RSS
{
    public static $typeName = 'Agenda';
    public static $typeDescription = 'Display an agenda from an RSS feed.';
    public static $html = '<div class="agenda" data-url="%data%"></div>';
    public static $input = 'url';
    public static $output = 'url';
    public static $usable = true;

    protected static function downloadFeed($url)
    {
        if (\Yii::$app->params['proxy']) {
            $ctx = [
                'http' => [
                    'proxy' => 'tcp://vdebian:8080',
                    'request_fulluri' => true,
                ],
            ];

            return file_get_contents($url, false, stream_context_create($ctx));
        } else {
            return file_get_contents($url);
        }
    }

    public static function processData($data)
    {
        $content = self::downloadFeed($data);

        $ical = new ICal();
        $ical->initString($content);

        $events = $ical->eventsFromRange('last monday', 'next friday');
        if (!count($events)) {
            return;
        }

        foreach ($events as $e) {
            // $e->summary // Name
            // $e->dtstart_array[2] // start timestamp
            // $e->dtend_array[2] // end timestamp
            // $e->description // group\teacher\export date
            // $e->location // room

            echo $e->summary.' '.$e->dtstart_array[2].' '.$e->dtend_array[2].' '.$e->description.' '.$e->location.'<br />';
        }
        exit;

        return $data;
    }
}
