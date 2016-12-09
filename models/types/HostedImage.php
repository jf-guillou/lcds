<?php

namespace app\models\types;

/**
 * This is the model class for HostedImage content type.
 */
class HostedImage extends Image
{
    public $input = 'file';
    public $usable = true;

    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->name = Yii::t('app', 'Hosted image');
        $this->description = Yii::t('app', 'Upload an image to servers.');
    }
}
