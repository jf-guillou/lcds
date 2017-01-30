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
        return array_merge([
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
        ], $this->type ? $this->type->contentRules() : []);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge([
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
        ], $this->type ? $this->type->contentLabels() : []);
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
     * Build a query for a specific user, allowing to see only authorized contents.
     *
     * @param \yii\web\User $user
     *
     * @return \yii\db\ActiveQuery
     */
    public static function availableQuery($user)
    {
        if ($user->can('setFlowContent')) {
            return self::find()->joinWith(['type']);
        } elseif ($user->can('setOwnFlowContent') && $user->identity instanceof \app\models\User) {
            return self::find()->joinWith(['type', 'flow.users'])->where(['username' => $user->identity->username]);
        }
    }

    /**
     * Check if a specific user is allowed to see this content.
     *
     * @param \yii\web\User $user
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
        return $this->type->processData($data);
    }

    /**
     * Retrieve data for content
     * Transforming it if necessary (mostly urls).
     *
     * @return string|null usable data
     */
    public function getData()
    {
        $data = $this->data;
        if ($this->type->appendParams) {
            $data .= (strpos($data, '#') === false ? '#' : ';').$this->type->appendParams;
        }

        $data = $this->processData($data);
        if ($data === null) {
            return null;
        }

        if ($this->type->html) {
            return str_replace('%data%', $data, $this->type->html);
        }

        return $data;
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $res = $this->type->transformDataBeforeSave($insert, $this->data);
        if ($res == null) {
            return false;
        }

        $this->data = $res;

        return true;
    }

    /**
     * After delete event
     * Try to delete file if necessary.
     */
    public function afterDelete()
    {
        if ($this->shouldDeleteFile()) {
            unlink($this->getRealFilepath());
        }
        parent::afterDelete();
    }

    /**
     * Decide if content file should be deleted by checking usage in DB.
     *
     * @return bool deletable
     */
    protected function shouldDeleteFile()
    {
        if ($this->type->input == ContentType::KINDS['FILE']) {
            return self::find()
                ->joinWith(['type'])
                ->where([ContentType::tableName().'.id' => ContentType::getAllFileTypeIds()])
                ->andWhere(['data' => $this->data])
                ->count() == 0;
        }

        return false;
    }

    /**
     * Get filepath from web root.
     *
     * @return string filepath
     */
    public function getFilepath()
    {
        $type = $this->type;

        return str_replace($type::BASE_URI, '', $this->getWebFilepath());
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
}
