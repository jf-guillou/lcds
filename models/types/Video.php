<?php

namespace app\models\types;

use Yii;

/**
 * This is the model class for Video content type.
 */
class Video extends Media
{
    const TYPE = 'video';
    const TYPE_PATH = 'videos/';

    public $html = '<iframe src="%data%" />';
    public $css = '%field% > iframe { height: 100%; width: 100%; }';
    public $appendParams = '_win=%x1%,%y1%,%x2%,%y2%;_aspect-mode=letterbox';
    public $usable = true;
    public $input = 'url';
    public $output = 'url';
    public $exemple = '@web/images/video.preview.jpg';

    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->name = Yii::t('app', 'Video');
        $this->description = Yii::t('app', 'Direct link to a video on an internet website. Hosted video is usually more appropriate.');
    }

    /**
     * {@inheritdoc}
     */
    public static function validateFile($realFilepath)
    {
        $mediainfo = self::getMediaInfo($realFilepath);

        if ($mediainfo && count($mediainfo->getVideos())) {
            return true;
        }

        return false;
    }
}
