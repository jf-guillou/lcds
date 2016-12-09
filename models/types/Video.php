<?php

namespace app\models\types;

/**
 * This is the model class for Video content type.
 */
class Video extends Media
{
    const TYPE = 'video';
    const TYPE_PATH = 'videos/';

    public $html = '<iframe src="%data%" />';
    public $css = '%field% > * { height: 100%; width: 100%; }';
    public $appendParams = '_win=%x1%,%y1%,%x2%,%y2%;_aspect-mode=letterbox';
    public $usable = false;
    public $input = 'url';
    public $output = 'url';
    public $preview = '@web/images/video.preview.jpg';

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
