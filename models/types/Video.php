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
}
