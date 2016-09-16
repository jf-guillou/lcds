<?php

namespace app\models\upload;

use Yii;

/**
 * This is the model class for image uploads.
 *
 * @property bitmap $content
 */
class ImageUpload extends ContentUpload
{
    const TYPE_PATH = 'images/';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content'], 'image', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'content' => Yii::t('app', 'Image'),
        ];
    }
}
