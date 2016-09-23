<?php

namespace app\models\upload;

use Mhor\MediaInfo\MediaInfo;

/**
 * This is the model class for content uploads.
 */
class ContentUpload extends \yii\base\Model
{
    const BASE_PATH = 'uploads/';
    const BASE_URI = '@web/';

    /**
     * @var UploadedFile
     */
    public $content;
    public $path;
    public $filename;
    public $size;
    public $mediaInfo;
    public $duration;
    public $error;

    public function upload()
    {
        if ($this->validate()) {
            $name = $this->content->baseName.'.'.$this->content->extension;
            $path = self::BASE_PATH.static::TYPE_PATH.$name;
            if ($this->content->saveAs($path)) {
                $this->path = self::BASE_URI.$path;

                return true;
            }
        }

        return false;
    }

    private function getInfo()
    {
        try {
            $this->mediaInfo = (new MediaInfo())->getInfo($this->getRealFilepath());
        } catch (\RuntimeException $e) {
            return;
        }
    }

    public function getGeneralInfo()
    {
        if (!$this->mediaInfo) {
            $this->getInfo();
        }

        if (!$this->mediaInfo) {
            return;
        }

        $general = $this->mediaInfo->getGeneral();
        if (!$general) {
            return;
        }

        return $general->get();
    }

    public function isValid()
    {
        $info = $this->getGeneralInfo();

        return $info !== null;
    }

    public function delete()
    {
        unlink($this->getRealFilepath());
    }

    public static function getPath()
    {
        return self::BASE_URI.self::BASE_PATH.static::TYPE_PATH;
    }

    public static function getRealPath()
    {
        return \Yii::getAlias('@app/').'web/'.self::BASE_PATH.static::TYPE_PATH;
    }

    public function getRealFilepath()
    {
        return self::toRealPath($this->path);
    }

    public static function toRealPath($filepath)
    {
        return \Yii::getAlias('@app/').str_replace(self::BASE_URI, 'web/', $filepath);
    }
}
