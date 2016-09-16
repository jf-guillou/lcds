<?php

namespace app\models\upload;

use Yii;
use yii\helpers\FileHelper;

/**
 * This is the model class for background uploads.
 *
 * @property bitmap $content
 */
class BackgroundUpload extends ContentUpload
{
    const TYPE_PATH = 'background/';

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
            'content' => Yii::t('app', 'Upload background'),
        ];
    }

    public static function getAllWithPath()
    {
        $files = FileHelper::findFiles(self::BASE_PATH.self::TYPE_PATH);

        $contents = [];
        foreach ($files as $f) {
            $contents[substr($f, strrpos($f, '/') + 1)] = static::BASE_URI.$f;
        }

        return $contents;
    }
}
