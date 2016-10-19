<?php

namespace app\models\types;

use app\models\Content;

/**
 * This is the model class for Ticker content type.
 */
class Ticker extends Text
{
    public static $typeName = 'Ticker';
    public static $typeDescription = 'Short text content, usually under 50 characters with a short duration.';
    public static $html = '<span class="ticker">%data%</span>';
    public static $css = '%field% { text-align: center; vertical-align: middle; }';
    public static $usable = true;
    public static $preview = '@web/images/ticker.preview.jpg';
}
