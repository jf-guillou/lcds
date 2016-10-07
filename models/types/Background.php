<?php

namespace app\models\types;

use yii\helpers\FileHelper;
use app\models\Content;

/**
 * This is the model class for content uploads.
 */
class Background extends Image
{
    const TYPE = 'image';
    const TYPE_PATH = 'background/';

    public static $typeName = 'Background';
    public static $usable = false;

    public static function getAllWithPath()
    {
        $files = FileHelper::findFiles(self::BASE_PATH.self::TYPE_PATH);

        $contents = [];
        foreach ($files as $f) {
            $contents[substr($f, strrpos($f, '/') + 1)] = static::BASE_URI.$f;
        }

        return $contents;
    }

    public function backgroundSet()
    {
        if ($this->tmp) {
            $this->tmp->delete();
        }
    }
}
