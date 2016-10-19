<?php

namespace app\models\types;

use app\models\Content;

/**
 * This is the model class for RSS content type.
 */
class RSS extends Content
{
    public static $typeName = 'RSS';
    public static $typeDescription = 'Display an RSS feed inline.';
    public static $html = '<div class="rss">%data%</div>';
    public static $input = 'url';
    public static $output = 'text';
    public static $usable = false;
    public static $preview = null;

    /**
     * Downloads feed from URL through proxy if necessary.
     *
     * @param string $url
     *
     * @return string feed content
     */
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

    /**
     * {@inheritdoc}
     */
    public function processData($data)
    {
        // Fetch content from cache if possible
        $content = self::fromCache($data);
        if (!$content) {
            $content = self::downloadFeed($data);
            self::toCache($data, $content, self::BASE_CACHE_TIME);
        }

        return nl2br(Html::encode($content));
    }
}
