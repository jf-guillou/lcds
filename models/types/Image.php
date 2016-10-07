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

    public static $typeName = 'Image';
    public static $typeDescription = 'Direct link to an image on an internet website. Hosted image is usually more appropriate.';
    public static $html = '<img src="%data%" class="image" />';
    public static $css = '%field% { text-align: center; vertical-align: middle; } %field% img { max-height: 100%; max-width: 100%; }';
    public static $kind = 'url';
    public static $usable = true;
}
