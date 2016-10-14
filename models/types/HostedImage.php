<?php

namespace app\models\types;

use app\models\Content;

/**
 * This is the model class for HostedImage content type.
 */
class HostedImage extends Image
{
    public static $typeName = 'Hosted image';
    public static $css = '%field% { text-align: center; vertical-align: middle; } %field% img { max-height: 100%; max-width: 100%; }';
    public static $input = 'file';
    public static $output = 'url';
    public static $usable = true;
}
