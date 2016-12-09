<?php

namespace app\models\types;

use app\models\ContentType;

/**
 * This is the model class for RSS content type.
 */
class RSS extends ContentType
{
    public $html = '<div class="rss">%data%</div>';
    public $input = 'url';
    public $output = 'text';
    public $usable = false;
    public $preview = null;

    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->name = Yii::t('app', 'RSS');
        $this->description = Yii::t('app', 'Display an RSS feed inline.');
    }

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
