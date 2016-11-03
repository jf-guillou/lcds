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
     * {@inheritdoc}
     */
    public function processData($data)
    {
        // Fetch content from cache if possible
        $content = self::fromCache($data);
        if (!$content) {
            $content = self::downloadContent($data);
            self::toCache($data, $content);
        }

        return nl2br(Html::encode($content));
    }
}
