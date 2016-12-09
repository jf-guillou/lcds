<?php

namespace app\models\types;

use Yii;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use YoutubeDl\YoutubeDl;

/**
 * This is the model class for HostedVideo content type.
 */
class HostedVideo extends Video
{
    public $input = 'file';
    public $usable = true;

    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->name = Yii::t('app', 'Hosted video');
        $this->description = Yii::t('app', 'Upload a video to servers.');
    }

    /**
     * {@inheritdoc}
     */
    public function sideload($url)
    {
        if (!self::validateUrl($url)) {
            $this->addError('load', Yii::t('app', 'Empty or incorrect URL'));

            return false;
        }

        $dl = new YoutubeDl([
            'proxy' => Yii::$app->params['proxy'],
            'format' => 'best[ext=mp4]/best[ext=flv]',
        ]);
        $dl->setDownloadPath(sys_get_temp_dir());

        try {
            $video = $dl->download($url);
            if (!$video) {
                $this->addError('load', Yii::t('app', 'Downloading failed'));

                return false;
            }
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
            $this->addError('load', Yii::t('app', 'Downloading error'));

            return false;
        } catch (\YoutubeDl\Exception\NotFoundException $e) {
            $this->addError('load', Yii::t('app', 'Media not found!'));

            return false;
        }

        $filename = $video->getFilename();
        $tmpFilepath = sys_get_temp_dir().'/'.$filename;

        $fileInstance = new UploadedFile();
        $fileInstance->name = $filename;
        $fileInstance->tempName = $tmpFilepath;
        $fileInstance->type = FileHelper::getMimeType($fileInstance->tempName);
        $fileInstance->size = $video->getFile()->getSize();
        $this->upload = $fileInstance;

        if (static::validateFile($fileInstance->tempName)) {
            return ['filename' => $filename, 'tmppath' => $tmpFilepath, 'duration' => static::getDuration($tmpFilepath)];
        }

        $this->addError('load', Yii::t('app', 'Invalid file'));
        unlink($fileInstance->tempName);

        return false;
    }
}
