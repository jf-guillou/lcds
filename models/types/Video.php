<?php

namespace app\models\types;

use app\models\Content;

/**
 * This is the model class for content uploads.
 */
class Video extends Media
{
    const TYPE = 'video';
    const TYPE_PATH = 'videos/';
}
