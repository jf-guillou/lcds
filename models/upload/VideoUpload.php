<?php

namespace app\models\upload;

use Yii;

/**
 * This is the model class for videos uploads.
 *
 * @property video $content
 */
class VideoUpload extends ContentUpload
{
    const TYPE_PATH = 'videos/';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content'], 'file', 'skipOnEmpty' => true, 'extensions' => 'avi, mp4, mkv'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'content' => Yii::t('app', 'Video'),
        ];
    }
}
