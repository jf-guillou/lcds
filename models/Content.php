<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "content".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $flow_id
 * @property string $type_id
 * @property string $data
 * @property int $duration
 * @property string $start_ts
 * @property string $end_ts
 * @property string $add_ts
 * @property bool $enabled
 * @property Flow $flow
 * @property ContentType $type
 */
class Content extends \yii\db\ActiveRecord
{
    const BASE_CACHE_TIME = 3600;
    const IS_FILE = false;
    const SUB_PATH = 'app\\models\\types\\';

    public static $typeName = null;
    public static $typeDescription = null;
    public static $html = null;
    public static $css = null;
    public static $js = null;
    public static $appendParams = null;
    public static $selfUpdate = false;
    public static $input = null;
    public static $output = null;
    public static $usable = false;
    public static $preview = null;
    public static $canPreview = false;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'content';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'flow_id', 'type_id'], 'required'],
            [['flow_id', 'duration'], 'integer'],
            [['data'], 'string'],
            [['start_ts', 'end_ts', 'add_ts'], 'safe'],
            [['enabled'], 'boolean'],
            [['name'], 'string', 'max' => 64],
            [['type_id'], 'string', 'max' => 45],
            [['description'], 'string', 'max' => 1024],
            [['flow_id'], 'exist', 'skipOnError' => true, 'targetClass' => Flow::className(), 'targetAttribute' => ['flow_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContentType::className(), 'targetAttribute' => ['type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'flow_id' => Yii::t('app', 'Flow'),
            'type_id' => Yii::t('app', 'Type'),
            'data' => Yii::t('app', 'Content'),
            'duration' => Yii::t('app', 'Duration in seconds'),
            'start_ts' => Yii::t('app', 'Start at'),
            'end_ts' => Yii::t('app', 'End on'),
            'add_ts' => Yii::t('app', 'Added at'),
            'enabled' => Yii::t('app', 'Enabled'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFlow()
    {
        return $this->hasOne(Flow::className(), ['id' => 'flow_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(ContentType::className(), ['id' => 'type_id']);
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
            throw new \Exception(Yii::t('app', 'Content type class does not exist'));
        }

        return $className;
    }

    /**
     * Instanciate a class from content type ID.
     *
     * @param string $typeId content type id
     *
     * @return mixed class instance
     */
    public static function newFromType($typeId)
    {
        $className = self::fromType($typeId);

        return new $className();
    }

    /**
     * Overloading default instanciate model.
     * Initialize a children class based on type_id column of $row.
     *
     * @param array $row
     *
     * @return mixed class instance
     */
    public static function instantiate($row)
    {
        $typeId = $row['type_id'];
        if (!$typeId) {
            throw new Exception(Yii::t('app', 'Content type class not set'));
        }

        $class = self::fromType($typeId);

        return new $class();
    }

    /**
     * Decide if content file should be deleted by checking usage in DB.
     *
     * @return bool deletable
     */
    protected function shouldDeleteFile()
    {
        return false;
    }

    /**
     * Build a query for a specific user, allowing to see only authorized contents.
     *
     * @param \User $user
     *
     * @return \yii\db\ActiveQuery
     */
    public static function availableQuery($user)
    {
        if ($user->can('setFlowContent')) {
            return self::find();
        } elseif ($user->can('setOwnFlowContent')) {
            return self::find()->joinWith(['flow.users'])->where(['username' => $user->identity->username]);
        }
    }

    /**
     * Check if a specific user is allowed to see this content.
     *
     * @param \User $user
     *
     * @return bool can see
     */
    public function canView($user)
    {
        if ($user->can('setFlowContent')) {
            return true;
        }
        if ($user->can('setOwnFlowContent') && in_array($user->identity, $this->flow->users)) {
            return true;
        }

        return false;
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
     * Get data directly from content
     * Used in view to preview/show data.
     *
     * @param string $data
     *
     * @return string preview data
     */
    public function get($data)
    {
        return;
    }

    /**
     * Retrieve data for content
     * Transforming it if necessary (mostly urls).
     *
     * @return string usable data
     */
    public function getData()
    {
        $data = $this->data;
        if ($this::$appendParams) {
            $data .= (strpos($data, '?') === false ? '?' : '&').$this::$appendParams;
        }

        $data = $this->processData($data);

        if ($this::$html) {
            return str_replace('%data%', $data, $this::$html);
        }

        return $data;
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
        $cacheKey = static::$typeName.$key;
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
        $cacheKey = static::$typeName.$key;
        $cache->set($cacheKey, $content, static::BASE_CACHE_TIME);
    }
}
