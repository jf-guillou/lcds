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
    public static $typeName = 'Hosted video';
    public static $typeDescription = 'Upload a video to servers.';
    public static $html = '<iframe src="%data%" />';
    public static $css = '%field% > * { height: 100%; width: 100%; }';
    public static $appendParams = '_win=%x1%,%y1%,%x2%,%y2%;_aspect-mode=letterbox';
    public static $input = 'file';
    public static $output = 'url';
    public static $usable = true;

    /**
     * {@inheritdoc}
     */
    public function sideload($url)
    {
        if (!self::validateUrl($url)) {
            $this->addError(static::TYPE, Yii::t('app', 'Empty or incorrect URL'));

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
                $this->addError(static::TYPE, Yii::t('app', 'Downloading failed'));

                return false;
            }
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
            $this->addError(static::TYPE, Yii::t('app', 'Downloading exception'));

            return false;
        }

        $this->filename = $video->getFilename();
        $tmpFilepath = sys_get_temp_dir().$this->filename;

        $fileInstance = new UploadedFile();
        $fileInstance->name = $this->filename;
        $fileInstance->tempName = $tmpFilepath;
        $fileInstance->type = FileHelper::getMimeType($fileInstance->tempName);
        $fileInstance->size = $video->getFile()->getSize();
        $this->upload = $fileInstance;

        if ($this->validate(['upload'])) {
            if (static::validateFile($fileInstance->tempName)) {
                return ['filename' => $this->filename, 'tmppath' => $tmpFilepath, 'duration' => static::getDuration($tmpFilepath)];
            }
            $this->addError(static::TYPE, Yii::t('app', 'Invalid file'));
        } else {
            $this->addError(static::TYPE, Yii::t('app', 'Cannot save file'));
        }
        unlink($fileInstance->tempName);

        return false;
    }
}
