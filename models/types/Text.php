<?php

namespace app\models\types;

use Yii;
use app\models\ContentType;
use yii\helpers\Html;

/**
 * This is the model class for Text content type.
 */
class Text extends ContentType
{
    public $html = '<span class="text bigtext">%data%</span>';
    public $css = '%field% { text-align: center; vertical-align: middle; }';
    public $input = 'text';
    public $output = 'text';
    public $usable = true;
    public $preview = '@web/images/text.preview.jpg';

    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->name = Yii::t('app', 'Text');
        $this->description = Yii::t('app', 'Textual content, will be adjusted to be displayed as big as possible.');
    }

    /**
     * {@inheritdoc}
     */
    public function processData($data)
    {
        return nl2br(Html::encode($data));
    }
}
