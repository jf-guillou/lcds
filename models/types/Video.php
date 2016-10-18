<?php

namespace app\models\types;

use app\models\Content;

/**
 * This is the model class for Video content type.
 */
class Video extends Media
{
    const TYPE = 'video';
    const TYPE_PATH = 'videos/';

    public static $usable = false;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['upload'], 'file', 'skipOnEmpty' => true, 'extensions' => 'avi, mp4, mkv'],
        ]);
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
