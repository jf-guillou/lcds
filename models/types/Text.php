<?php

namespace app\models\types;

use app\models\ContentType;
use yii\helpers\Html;

/**
 * This is the model class for Text content type.
 */
class Text extends ContentType
{
    public $name = 'Text';
    public $description = 'Textual content, will be adjusted to be displayed as big as possible.';
    public $html = '<span class="text">%data%</span>';
    public $css = '%field% { text-align: center; vertical-align: middle; }';
    public $input = 'text';
    public $output = 'text';
    public $usable = true;
    public $preview = '@web/images/text.preview.jpg';

    /**
     * {@inheritdoc}
     */
    public function processData($data)
    {
        return nl2br(Html::encode($data));
    }
}
