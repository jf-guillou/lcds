<?php

namespace app\models\upload;

use Yii;
use YoutubeDl\YoutubeDl;

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
            [['content'], 'required'],
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

    public function sideload($url)
    {
        $dl = new YoutubeDl([
            'proxy' => Yii::$app->params['proxy'],
            'format' => 'best[ext=mp4]/best[ext=flv]',
        ]);
        $dl->setDownloadPath(self::getRealPath());

        try {
            $video = $dl->download($url);
            if (!$video) {
                $this->error = Yii::t('app', 'Downloading failed');

                return false;
            }
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
            $this->error = Yii::t('app', 'Downloading exception');

            return false;
        }

        $this->filename = $video->getFilename();
        $this->path = self::getPath().$this->filename;
        $this->size = $video->getFile()->getSize();
        $this->duration = $video->getDuration();

        return true;
    }
}
