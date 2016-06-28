<?php

namespace app\models;

use yii\web\UploadedFile;

/**
 * This is the model class for image uploads.
 *
 * @property bitmap $image
 */
class ImageUpload extends \yii\base\Model
{
    /**
     * @var UploadedFile
     */
    public $image;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['image'], 'image', 'skipOnEmpty' => false, 'extensions' => 'png, jpg'],
        ];
    }

    public function upload()
    {
        if ($this->validate()) {
            $path = 'uploads/'.$this->image->baseName.'.'.$this->image->extension;
            if ($this->image->saveAs($path)) {
                return $path;
            }
        }

        return false;
    }
}
