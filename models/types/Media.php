<?php

namespace app\models\types;

use Yii;
use Mhor\MediaInfo\MediaInfo;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use yii\web\UploadedFile;
use app\models\Content;
use app\models\ContentType;

/**
 * This is the model class for Media content type.
 *
 * @property \FileInstance $upload
 * @property  int $size
 * @property  filename $string
 */
class Media extends Content
{
    const IS_FILE = true;
    const BASE_PATH = 'uploads/';
    const TYPE_PATH = 'tmp/';
    const BASE_URI = '@web/';
    public static $usable = false;
    public static $canPreview = true;

    public $upload;
    public $size;
    public $filename;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['filename'], 'string', 'min' => 1, 'max' => 128],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return parent::attributeLabels() + [
            'upload' => Yii::t('app', 'Upload'),
        ];
    }

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
            $this->addError('upload', Yii::t('app', 'There\'s no file'));

            return false;
        }

        $this->upload = $fileInstance;

        if ($this->validate(['upload'])) {
            $this->filename = $this->upload->baseName.'.'.$this->upload->extension;
            $tmpFilepath = tempnam(sys_get_temp_dir(), 'LCDS_');

            if ($this->upload->saveAs($tmpFilepath)) {
                if (static::validateFile($tmpFilepath)) {
                    return ['filename' => $this->filename, 'tmppath' => $tmpFilepath, 'duration' => static::getDuration($tmpFilepath)];
                }
                $this->addError('upload', Yii::t('app', 'Invalid file'));
                unlink($tmpFilepath);

                return false;
            }
            $this->addError('upload', Yii::t('app', 'Cannot save file'));
        }

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
     * Take an url and download it, also save it in DB.
     *
     * @param string $url
     *
     * @return bool|string[] error or json success string
     */
    public function sideload($url)
    {
        if (!self::validateUrl($url)) {
            $this->addError(static::TYPE, Yii::t('app', 'Empty or incorrect URL'));

            return false;
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_PROXY, Yii::$app->params['proxy']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, [&$this, 'readHeaderFilename']);
        $fileContent = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            $this->addError('upload', Yii::t('app', $error));

            return false;
        }

        if (!$this->filename) {
            $urlSplit = explode('/', $url);
            $this->filename = $urlSplit[count($urlSplit) - 1];
        }

        $tmpFilepath = tempnam(sys_get_temp_dir(), 'LCDS_');

        $file = fopen($tmpFilepath, 'w+');
        fputs($file, $fileContent);
        fclose($file);

        $fileInstance = new UploadedFile();
        $fileInstance->name = $this->filename;
        $fileInstance->tempName = $tmpFilepath;
        $fileInstance->type = FileHelper::getMimeType($fileInstance->tempName);
        $fileInstance->size = $this->size;
        $this->upload = $fileInstance;

        if ($this->validate(['upload'])) {
            if (static::validateFile($fileInstance->tempName)) {
                return ['filename' => $this->filename, 'tmppath' => $tmpFilepath, 'duration' => static::getDuration($tmpFilepath)];
            }
            $this->addError('upload', Yii::t('app', 'Invalid file'));
        }
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
            $this->size = intval(trim($matches[1]));
        } elseif (strpos($header, 'Content-Disposition:') === 0 && preg_match('/filename=(.*)$/', $header, $matches)) {
            $this->filename = trim(str_replace('"', '', $matches[1]));
        }

        return strlen($header);
    }

    /**
     * Custom error getter for upload/sideload temp file.
     *
     * @return string error
     */
    public function getLoadError()
    {
        $errors = $this->getErrors();
        if (array_key_exists('upload', $errors) && count($errors['upload'])) {
            return implode(' - ', $errors['upload']);
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
        return $this->data;
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
     * @return int media duration
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
     * Before save event
     * Handles file movement from tmp directory to proper media storage
     * Makes sure there is no overwrite by appending to filename.
     *
     * @param bool $insert is inserted
     *
     * @return bool success
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $tmppath = FileHelper::normalizePath($this->data);

                $parts = explode(DIRECTORY_SEPARATOR, $tmppath);
                $tmpname = array_pop($parts);
                if (implode(DIRECTORY_SEPARATOR, $parts) == sys_get_temp_dir() && strpos($tmpname, 'LCDS_') === 0 && strpos(DIRECTORY_SEPARATOR, $this->filename) === false && file_exists($tmppath)) {
                    $this->filename = static::getUniqFilename(static::getRealPath(), $this->filename);
                    $this->data = static::getWebPath().$this->filename;
                    $realFilepath = static::getRealPath().$this->filename;

                    return rename($tmppath, $realFilepath);
                }

                return false;
            }

            return true;
        }

        return false;
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
            ++$i;
        }

        return $filename;
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
