<?php

namespace app\models\upload;

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

    public function upload()
    {
        if ($this->validate() && isset($this->content)) {
            $name = $this->content->baseName.'.'.$this->content->extension;
            $path = self::BASE_PATH.static::TYPE_PATH.$name;
            if ($this->content->saveAs($path)) {
                return self::BASE_URI.$path;
            }
        }

        return false;
    }
}
