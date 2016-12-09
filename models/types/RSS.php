<?php

namespace app\models\types;

use app\models\ContentType;

/**
 * This is the model class for RSS content type.
 */
class RSS extends ContentType
{
    public $name = 'RSS';
    public $description = 'Display an RSS feed inline.';
    public $html = '<div class="rss">%data%</div>';
    public $input = 'url';
    public $output = 'text';
    public $usable = false;
    public $preview = null;

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
