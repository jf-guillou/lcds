<?php

namespace app\models;

use yii\web\UploadedFile;
use yii\helpers\FileHelper;

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

    const BASE_PATH = 'uploads/';
    const TYPES = ['background'];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['image'], 'image', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
        ];
    }

    public function upload($type)
    {
        if ($this->validate() && isset($this->image)) {
            $img = $this->image->baseName.'.'.$this->image->extension;
            $path = self::BASE_PATH.self::typePath($type).$img;
            if ($this->image->saveAs($path)) {
                return $img;
            }
        }

        return false;
    }

    public static function getImage($type, $name)
    {
        return 'uploads/'.self::typePath($type).$name;
    }

    public static function getImages($type)
    {
        $files = FileHelper::findFiles('uploads/'.self::typePath($type));

        $images = [];
        foreach ($files as $f) {
            $images[] = substr($f, strrpos($f, '/') + 1);
        }

        return $images;
    }

    public static function getImagesWithPath($type)
    {
        $files = FileHelper::findFiles('uploads/'.self::typePath($type));

        $images = [];
        foreach ($files as $f) {
            $images[substr($f, strrpos($f, '/') + 1)] = $f;
        }

        return $images;
    }

    public static function typePath($type)
    {
        if (in_array($type, self::TYPES)) {
            $type .= '/';
        } else {
            $type = '';
        }

        return $type;
    }
}
