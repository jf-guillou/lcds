<?php

namespace app\models;

use Yii;
use Mhor\MediaInfo\MediaInfo;

/**
 * This is the model class for table "temp_file".
 *
 * @property int $id
 * @property string $file
 * @property string $added_at
 */
class TempFile extends \yii\db\ActiveRecord
{
    public $name;
    public $size;
    public $image;
    public $video;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'temp_file';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file'], 'required'],
            [['id'], 'integer'],
            [['file'], 'string'],
            [['added_at'], 'safe'],
            [['image'], 'image', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, gif'],
            [['video'], 'file', 'skipOnEmpty' => true, 'extensions' => 'avi, mp4, mkv'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'file' => Yii::t('app', 'File'),
            'added_at' => Yii::t('app', 'Added At'),
        ];
    }

    public function validateFile($realPath)
    {
        $mediainfo = null;
        try {
            $mediainfo = (new MediaInfo())->getInfo($realPath);
        } catch (\RuntimeException $e) {
            return false;
        }

        if (count($mediainfo->getVideos()) || count($mediainfo->getImages())) {
            return true;
        }

        return false;
    }

    public function readHeaderFilename($curl, $header)
    {
        if (strpos($header, 'Content-Length:') === 0) {
            if (preg_match('/(\d+)/', $header, $matches)) {
                $this->size = trim($matches[1]);
            }
        } elseif (strpos($header, 'Content-Disposition:') === 0) {
            if (preg_match('/filename=(.*)$/', $header, $matches)) {
                $this->name = trim(str_replace('"', '', $matches[1]));
            }
        }

        return strlen($header);
    }
}
