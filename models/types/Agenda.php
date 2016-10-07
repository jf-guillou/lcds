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
        $ical->defaultWeekStart = 'MO';
        $ical->initString($content);

        $events = $ical->eventsFromRange('last monday', 'next friday');
        if (!count($events)) {
            return;
        }

        var_dump($events[0]);
        exit;

        foreach ($events as $e) {
            $e['summary']; // name
            $e['dtstart'];
            $e['dtend'];
            $e['description'];
            $e['location'];
        }
        exit;

        return $data;
    }
}
