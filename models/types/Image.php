<?php

namespace app\models\types;

use app\models\Content;

/**
 * This is the model class for content uploads.
 */
class Image extends Media
{
    const TYPE = 'image';
    const TYPE_PATH = 'images/';
}
