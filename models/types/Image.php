<?php

namespace app\models\types;

use Yii;

/**
 * This is the model class for Image content type.
 */
class Image extends Media
{
    const TYPE = 'image';
    const TYPE_PATH = 'images/';

    public $html = '<img src="%data%" class="image" />';
    public $css = '%field% { text-align: center; } %field% img { position: absolute; top: 0; left: 0; bottom: 0; right: 0; margin: auto; max-height: 100%; max-width: 100%; }';
    public $input = 'url';
    public $output = 'url';
    public $usable = true;
    public $exemple = '@web/images/image.preview.jpg';

    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->name = Yii::t('app', 'Image');
        $this->description = Yii::t('app', 'Direct link to an image on an internet website. Hosted image is usually more appropriate.');
    }

    public function transformDataBeforeSave($insert, $data)
    {
        return $data;
    }
}
