<?php

namespace app\models\types;

use Yii;
use Mhor\MediaInfo\MediaInfo;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\UploadedFile;
use app\models\Content;
use app\models\ContentType;
use app\models\TempFile;

/**
 * This is the model class for Media content type.
 */
class Media extends Content
{
    const IS_FILE = true;
    const BASE_PATH = 'uploads/';
    const BASE_URI = '@web/';
    public static $usable = false;

    // Temp file instance
    protected $tmp;

    /**
     * Take a file instance and upload it to FS, also save in DB.
     *
     * @param \FileInstance $fileInstance
     *
     * @return bool success
     */
    public function upload($fileInstance)
    {
        if ($fileInstance === null) {
            return false;
        }

        $this->tmp = new TempFile();
        $type = static::TYPE;
        $this->tmp->$type = $fileInstance;
        $this->tmp->name = $fileInstance->baseName.'.'.$fileInstance->extension;
        $this->tmp->file = self::getWebPath().$this->tmp->name;

        if ($this->tmp->validate() && $this->tmp->save()) {
            if ($this->tmp->$type->saveAs(self::getPath().$this->tmp->name)) {
                if ($this->tmp->validateFile(self::getRealPath().$this->tmp->name)) {
                    return true;
                }
                $this->tmp->delete();
            }
        }

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
     * Take an url and download it, also save it in DB.
     *
     * @param string $url
     *
     * @return bool success
     */
    public function sideload($url)
    {
        if (!self::validateUrl($url)) {
            $this->addError(static::TYPE, Yii::t('app', 'Empty or incorrect URL'));

            return false;
        }

        $this->tmp = new TempFile();
        $type = static::TYPE;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_PROXY, Yii::$app->params['proxy']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, [&$this->tmp, 'readHeaderFilename']);
        $fileContent = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            $this->addError(static::TYPE, Yii::t('app', $error));

            return false;
        }

        if (!$this->tmp->name) {
            $urlSplit = explode('/', $url);
            $this->tmp->name = $urlSplit[count($urlSplit) - 1];
        }

        $destination = self::getRealPath().$this->tmp->name;
        $i = 0;
        while (file_exists($destination)) {
            $destination = self::getRealPath().++$i.$this->tmp->name;
        }
        if ($i > 0) {
            $this->tmp->name = $i.$this->tmp->name;
        }

        $this->tmp->file = self::getWebPath().$this->tmp->name;

        $file = fopen($destination, 'w+');
        fputs($file, $fileContent);
        fclose($file);

        $fileInstance = new UploadedFile();
        $fileInstance->name = $this->tmp->name;
        $fileInstance->tempName = self::getRealPath().$fileInstance->name;
        $fileInstance->type = FileHelper::getMimeType($fileInstance->tempName);
        $fileInstance->size = $this->tmp->size;
        $this->tmp->$type = $fileInstance;

        if ($this->tmp->validate() && $this->tmp->save()) {
            if ($this->tmp->validateFile($fileInstance->tempName)) {
                return true;
            }
            $this->addError(static::TYPE, Yii::t('app', 'Invalid file'));
            $this->tmp->delete();
        } else {
            $this->addError(static::TYPE, Yii::t('app', 'Cannot save file'));
        }
        unlink($fileInstance->tempName);

        return false;
    }

    /**
     * Custom error getter for upload/sideload temp file.
     *
     * @return string error
     */
    public function getLoadError()
    {
        $type = static::TYPE;
        if ($this->tmp) {
            $errors = $this->tmp->getErrors();
            if (count($errors[$type])) {
                return implode(' - ', $errors[$type]);
            }
        }
        $errors = $this->getErrors();
        if (count($errors[$type])) {
            return implode(' - ', $errors[$type]);
        }

        return Yii::t('app', 'Incorrect file');
    }

    /**
     * Get filepath from web root.
     *
     * @return string filepath
     */
    public function getFilepath()
    {
        return str_replace(self::BASE_URI, '', $this->getWebFilepath());
    }

    /**
     * Get Yii aliased filepath.
     *
     * @return string filepath
     */
    public function getWebFilepath()
    {
        return $this->data ?: self::getWebPath().$this->tmp->name;
    }

    /**
     * Get filesystem filepath.
     *
     * @return string filepath
     */
    public function getRealFilepath()
    {
        return \Yii::getAlias('@app/').'web/'.$this->getFilepath();
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
     * {@inheritdoc}
     */
    protected function shouldDeleteFile()
    {
        if (static::IS_FILE) {
            return self::find()
                ->joinWith(['type'])
                ->where([ContentType::tableName().'.id' => ContentType::getAllFileTypeIds()])
                ->andWhere(['data' => $this->data])
                ->count() == 0;
        }

        return false;
    }

    /**
     * Try to get media info for this media.
     *
     * @return \MediaInfo|null media info
     */
    protected function getMediaInfo()
    {
        try {
            return (new MediaInfo())->getInfo($this->getRealFilepath());
        } catch (\RuntimeException $e) {
            return;
        }
    }

    /**
     * Get media duration.
     *
     * @return int duration
     */
    public function getDuration()
    {
        return;
    }

    /**
     * After save event
     * Try to delete tempfile model for this file.
     *
     * @param bool  $insert            is model inserted
     * @param array $changedAttributes
     *
     * @return bool success
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (parent::afterSave($insert, $changedAttributes)) {
            if ($insert) {
                TempFile::find()->where(['file' => $this->data])->delete();
            }

            return true;
        }

        return false;
    }

    /**
     * After delete event
     * Try to delete file if necessary.
     *
     * @return bool success
     */
    public function afterDelete()
    {
        if ($this->shouldDeleteFile()) {
            unlink($this->getRealFilepath());
        }
        parent::afterDelete();
    }

    /**
     * {@inheritdoc}
     */
    public function processData($data)
    {
        return Url::to($data);
    }
}
