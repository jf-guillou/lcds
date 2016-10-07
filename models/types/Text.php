<?php

namespace app\models\types;

use app\models\Content;
use yii\helpers\Html;

/**
 * This is the model class for content uploads.
 */
class Text extends Content
{
    public static $typeName = 'Text';
    public static $typeDescription = 'Textual content, will be adjusted to be displayed as big as possible.';
    public static $html = '<span class="text">%data%</span>';
    public static $css = '%field% { text-align: center; vertical-align: middle; }';
    public static $kind = 'text';
    public static $usable = true;

    public static function processData($data)
    {
        return nl2br(Html::encode($data));
    }
}
