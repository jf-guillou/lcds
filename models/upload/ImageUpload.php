<?php

namespace app\models\upload;

use Yii;

/**
 * This is the model class for image uploads.
 *
 * @property bitmap $content
 */
class ImageUpload extends ContentUpload
{
    const TYPE_PATH = 'images/';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content'], 'required'],
            [['content'], 'image', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, gif'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'content' => Yii::t('app', 'Image'),
        ];
    }

    public function sideload($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_PROXY, Yii::$app->params['proxy']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, [&$this, 'readHeaderFilename']);
        $fileContent = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            $this->error = $error;

            return false;
        }

        if (!$this->filename) {
            $urlSplit = explode('/', $url);
            $this->filename = $urlSplit[count($urlSplit) - 1];
        }

        $destination = self::getRealPath().$this->filename;
        $i = 0;
        while (file_exists($destination)) {
            $destination = self::getRealPath().++$i.$this->filename;
        }
        if ($i > 0) {
            $this->filename = $i.$this->filename;
        }

        $this->path = self::getPath().$this->filename;

        $file = fopen($destination, 'w+');
        fputs($file, $fileContent);
        fclose($file);

        return true;
    }

    public function readHeaderFilename($curl, $header)
    {
        if (strpos($header, 'Content-Length:') === 0) {
            if (preg_match('/(\d+)/', $header, $matches)) {
                $this->size = trim($matches[1]);
            }
        } elseif (strpos($header, 'Content-Disposition:') === 0) {
            if (preg_match('/filename=(.*)$/', $header, $matches)) {
                $this->filename = trim(str_replace('"', '', $matches[1]));
            }
        }

        return strlen($header);
    }
}
