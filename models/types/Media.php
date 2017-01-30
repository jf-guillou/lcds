<?php

namespace app\models\types;

use Yii;
use Mhor\MediaInfo\MediaInfo;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\UploadedFile;
use app\models\ContentType;

/**
 * This is the model class for Media content type.
 *
 * @property \FileInstance $upload
 * @property  int $size
 */
abstract class Media extends ContentType
{
    const BASE_PATH = 'uploads/';
    const TYPE_PATH = 'tmp/';
    const BASE_URI = '@web/';
    public $usable = false;
    public $canPreview = true;

    public $upload;
    public $_size;
    public $_filename;

    /**
     * {@inheritdoc}
     */
    public function upload($fileInstance)
    {
        if ($fileInstance === null) {
            $this->addError('load', Yii::t('app', 'There\'s no file'));

            return false;
        }

        $this->upload = $fileInstance;

        $filename = $this->upload->baseName.'.'.$this->upload->extension;
        $tmpFilepath = tempnam(sys_get_temp_dir(), 'LCDS_');

        if ($this->upload->saveAs($tmpFilepath)) {
            if (static::validateFile($tmpFilepath)) {
                return ['filename' => $filename, 'tmppath' => $tmpFilepath, 'duration' => static::getDuration($tmpFilepath)];
            }
            $this->addError('load', Yii::t('app', 'Invalid file'));
            unlink($tmpFilepath);

            return false;
        }
        $this->addError('load', Yii::t('app', 'Cannot save file'));

        return false;
    }

    /**
     * Custom file validation based on mediainfo description.
     *
     * @param string $realFilepath filesystem path
     *
     * @return bool is file valid
     */
    public static function validateFile($realFilepath)
    {
        return false;
    }

    /**
     * Validate an url based on PHP filter_var.
     *
     * @param string $url
     *
     * @return bool valid
     */
    public static function validateUrl($url)
    {
        return $url && filter_var($url, FILTER_VALIDATE_URL);
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

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if (Yii::$app->params['proxy']) {
            curl_setopt($curl, CURLOPT_PROXY, Yii::$app->params['proxy']);
        }
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, [&$this, 'readHeaderFilename']);
        $fileContent = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            $this->addError('load', Yii::t('app', $error));

            return false;
        }

        $filename = $this->_filename;
        if (!$filename) {
            $urlSplit = explode('/', $url);
            $filename = $urlSplit[count($urlSplit) - 1];
        }

        $tmpFilepath = tempnam(sys_get_temp_dir(), 'LCDS_');

        $file = fopen($tmpFilepath, 'w+');
        fwrite($file, $fileContent);
        fclose($file);

        $fileInstance = new UploadedFile();
        $fileInstance->name = $filename;
        $fileInstance->tempName = $tmpFilepath;
        $fileInstance->type = FileHelper::getMimeType($fileInstance->tempName);
        $fileInstance->size = $this->_size;
        $this->upload = $fileInstance;

        if (static::validateFile($fileInstance->tempName)) {
            return ['filename' => $filename, 'tmppath' => $tmpFilepath, 'duration' => static::getDuration($tmpFilepath)];
        }

        $this->addError('load', Yii::t('app', 'Invalid file'));
        unlink($fileInstance->tempName);

        return false;
    }

    /**
     * Header parsing method used to get size & filename.
     *
     * @param mixed  $curl   curl handler
     * @param string $header
     *
     * @return int header length
     */
    public function readHeaderFilename($curl, $header)
    {
        if (strpos($header, 'Content-Length:') === 0 && preg_match('/(\d+)/', $header, $matches)) {
            $this->_size = intval(trim($matches[1]));
        } elseif (strpos($header, 'Content-Disposition:') === 0 && preg_match('/filename=(.*)$/', $header, $matches)) {
            $this->_filename = trim(str_replace('"', '', $matches[1]));
        }

        return strlen($header);
    }

    /**
     * {@inheritdoc}
     */
    public function getLoadError()
    {
        $errors = $this->getErrors();
        if (array_key_exists('load', $errors) && count($errors['load'])) {
            return implode(' - ', $errors['load']);
        }

        return Yii::t('app', 'Incorrect file');
    }

    /**
     * Get storage path from web root.
     *
     * @return string path
     */
    public static function getPath()
    {
        return self::BASE_PATH.static::TYPE_PATH;
    }

    /**
     * Get Yii aliased storage path.
     *
     * @return string path
     */
    public static function getWebPath()
    {
        return self::BASE_URI.self::getPath();
    }

    /**
     * Get filesystem storage path.
     *
     * @return string path
     */
    public static function getRealPath()
    {
        return \Yii::getAlias('@app/').'web/'.self::getPath();
    }

    /**
     * Try to get media info for this media.
     *
     * @return \Mhor\MediaInfo\Container\MediaInfoContainer|null media info
     */
    protected static function getMediaInfo($realFilepath)
    {
        try {
            return (new MediaInfo())->getInfo($realFilepath);
        } catch (\RuntimeException $e) {
            return;
        }
    }

    /**
     * Use mediainfo to parse media duration.
     *
     * @param string $realFilepath
     *
     * @return int|null media duration
     */
    public static function getDuration($realFilepath)
    {
        $mediainfo = static::getMediaInfo($realFilepath);
        if ($mediainfo) {
            $general = $mediainfo->getGeneral();
            if ($general && $general->get('duration')) {
                return ceil($general->get('duration')->getMilliseconds() / 1000);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function transformDataBeforeSave($insert, $data)
    {
        if ($insert) {
            list($path, $filename) = explode('§', $data);
            $tmppath = FileHelper::normalizePath($path);

            $parts = explode(DIRECTORY_SEPARATOR, $tmppath);
            array_pop($parts); // Remove filename
            if (implode(DIRECTORY_SEPARATOR, $parts) == sys_get_temp_dir() && strpos(DIRECTORY_SEPARATOR, $filename) === false && file_exists($tmppath)) {
                $filename = static::getUniqFilename(static::getRealPath(), $filename);
                $data = static::getWebPath().$filename;
                $realFilepath = static::getRealPath().$filename;

                if (rename($tmppath, $realFilepath)) {
                    return $data;
                }
            }

            return null;
        }

        return $data;
    }

    /**
     * Create unique filename by checking for existence and appending to filename.
     *
     * @param string $path     filepath
     * @param string $filename
     *
     * @return string unique filename
     */
    protected static function getUniqFilename($path, $filename)
    {
        if (!file_exists($path.$filename)) {
            return $filename;
        }

        $parts = explode('.', $filename);
        if (count($parts) > 1) {
            $ext = array_pop($parts);
        } else {
            $ext = null;
        }
        $name = implode('.', $parts);

        $i = 1;
        $filename = $name.$i.($ext ? '.'.$ext : '');
        while (file_exists($path.$filename)) {
            $filename = $name.++$i.($ext ? '.'.$ext : '');
        }

        return $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function processData($data)
    {
        return Url::to($data);
    }
}
