<?php

namespace app\models\types;

/**
 * This is the model class for Ticker content type.
 */
class Ticker extends Text
{
    public $name = 'Ticker';
    public $description = 'Short text content, usually under 50 characters with a short duration.';
    public $html = '<span class="ticker">%data%</span>';
    public $css = '%field% { text-align: center; vertical-align: middle; }';
    public $usable = true;
    public $preview = '@web/images/ticker.preview.jpg';
}
