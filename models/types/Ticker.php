<?php

namespace app\models\types;

use Yii;

/**
 * This is the model class for Ticker content type.
 */
class Ticker extends Text
{
    public $html = '<span class="ticker bigtext">%data%</span>';
    public $css = '%field% { text-align: center; vertical-align: middle; }';
    public $usable = true;
    public $preview = '@web/images/ticker.preview.jpg';

    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->name = Yii::t('app', 'Ticker');
        $this->description = Yii::t('app', 'Short text content, usually under 50 characters with a short duration.');
    }
}
