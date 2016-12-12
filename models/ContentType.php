<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "content_type".
 *
 * @property string $id Class name
 * @property Content[] $contents
 * @property FieldHasContentType[] $fieldHasContentTypes
 * @property Field[] $fields
 */
class ContentType extends \yii\db\ActiveRecord
{
    const BASE_CACHE_TIME = 3600;
    const SUB_PATH = 'app\\models\\types\\';

    public $name;
    public $description;
    public $html;
    public $css;
    public $js;
    public $appendParams;
    public $selfUpdate;
    public $input;
    public $output;
    public $usable;
    public $preview;
    public $canPreview;

    const KINDS = [
        'NONE' => 'none',
        'RAW' => 'raw',
        'URL' => 'url',
        'FILE' => 'file',
        'TEXT' => 'text',
        'POS' => 'latlong',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'content_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'enabled'], 'required'],
            [['id'], 'string', 'max' => 45],
            [['enabled'], 'boolean'],
        ];
    }

    public function contentRules()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Type'),
            'description' => Yii::t('app', 'Description'),
            'usable' => Yii::t('app', 'Usable'),
            'enabled' => Yii::t('app', 'Enabled'),
            'html' => Yii::t('app', 'HTML'),
            'css' => Yii::t('app', 'CSS'),
            'js' => Yii::t('app', 'JS'),
        ];
    }

    public function contentLabels()
    {
        return [];
    }

    /**
     * Get class from content type ID.
     *
     * @param string $typeId content type id
     *
     * @return string class name
     */
    public static function fromType($typeId)
    {
        $className = self::SUB_PATH.$typeId;
        if (!class_exists($className)) {
            throw new ServerErrorHttpException(Yii::t('app', 'The requested content type has no class.'));
        }

        return $className;
    }

    /**
     * Overload default instantiate to fill attributes from specific content type.
     *
     * @param array $row
     *
     * @return array transformed row
     */
    public static function instantiate($row)
    {
        $typeClass = self::fromType($row['id']);

        return new $typeClass();
    }

    /**
     * Get raw data and transform it to content type specific needs.
     *
     * @param string $data
     *
     * @return string transformed data
     */
    public function processData($data)
    {
        return $data;
    }

    /**
     * Get all filtered content types.
     *
     * @param bool $selfUpdate does content type manages itself
     * @param bool $usableOnly show only usable content types
     *
     * @return array content types
     */
    public static function getAll($selfUpdate = null, $usableOnly = true)
    {
        $types = self::find()->all();

        return array_filter($types, function ($t) use ($selfUpdate, $usableOnly) {
            return ($selfUpdate === null || $t->selfUpdate == $selfUpdate) && (!$usableOnly || $t->usable);
        });
    }

    /**
     * Get all filterd content types in array.
     *
     * @param bool $selfUpdate does content type manages itself
     * @param bool $usableOnly show only usable content types
     *
     * @return array content types
     */
    public static function getAllList($selfUpdate = null, $usableOnly = true)
    {
        $types = self::getAll($selfUpdate, $usableOnly);

        $list = [];

        foreach ($types as $t) {
            $list[$t->id] = $t->name;
        }

        return $list;
    }

    /**
     * Get all file based content types.
     *
     * @return array content types
     */
    public static function getAllFileTypeIds()
    {
        $types = self::find()->all();

        return array_filter(array_map(function ($t) {
            return $t->input == self::KINDS['FILE'] ? $t->id : null;
        }, $types));
    }

    /**
     * Downloads content from URL through proxy if necessary.
     *
     * @param string $url
     *
     * @return string content
     */
    public static function downloadContent($url)
    {
        if (\Yii::$app->params['proxy']) {
            $ctx = [
                'http' => [
                    'proxy' => 'tcp://vdebian:8080',
                    'request_fulluri' => true,
                ],
            ];

            return file_get_contents($url, false, stream_context_create($ctx));
        } else {
            return file_get_contents($url);
        }
    }

    /**
     * Check cache existence.
     *
     * @param string $key cache key
     *
     * @return bool has cached data
     */
    public function hasCache($key)
    {
        return \Yii::$app->cache->exists($this->id.$key);
    }

    /**
     * Get from cache.
     *
     * @param string $key cache key
     *
     * @return string cached data
     */
    public function fromCache($key)
    {
        $cache = \Yii::$app->cache;
        $cacheKey = $this->id.$key;
        if ($cache->exists($cacheKey)) {
            return $cache->get($cacheKey);
        }
    }

    /**
     * Store to cache.
     *
     * @param string $key     cache key
     * @param string $content cache data
     */
    public function toCache($key, $content)
    {
        $cache = \Yii::$app->cache;
        $cacheKey = $this->id.$key;
        $cache->set($cacheKey, $content, static::BASE_CACHE_TIME);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContents()
    {
        return $this->hasMany(Content::className(), ['type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFieldHasContentTypes()
    {
        return $this->hasMany(FieldHasContentType::className(), ['content_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFields()
    {
        return $this->hasMany(Field::className(), ['id' => 'field_id'])->viaTable('field_has_content_type', ['content_type_id' => 'id']);
    }

    /**
     * File management methods.
     */

    /**
     * Take a file instance and upload it to FS, also save in DB.
     *
     * @param \yii\web\UploadedFile $fileInstance
     *
     * @return bool|array error or json success string
     */
    public function upload($fileInstance)
    {
        return false;
    }

    /**
     * Take an url and download it, also save it in DB.
     *
     * @param string $url
     *
     * @return bool|array error or json success string
     */
    public function sideload($url)
    {
        return false;
    }

    /**
     * Custom error getter for upload/sideload temp file.
     *
     * @return string error
     */
    public function getLoadError()
    {
        return '';
    }

    /**
     * Transform data on beforeSave event.
     *
     * @param bool   $insert is inserted
     * @param string $data   content data
     *
     * @return string transformed data
     */
    public function transformDataBeforeSave($insert, $data)
    {
        return $data;
    }
}
