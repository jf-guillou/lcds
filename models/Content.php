<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "content".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $type_id
 * @property string $data
 * @property int $duration
 * @property string $start_ts
 * @property string $end_ts
 * @property string $add_ts
 * @property bool $enabled
 * @property bool $editable
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
        return [
            [['name', 'flow_id', 'type_id'], 'required'],
            [['flow_id', 'type_id', 'duration'], 'integer'],
            [['data'], 'string'],
            [['start_ts', 'end_ts', 'add_ts'], 'safe'],
            [['enabled', 'editable'], 'boolean'],
            [['name'], 'string', 'max' => 64],
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
            'type_id' => Yii::t('app', 'Type ID'),
            'data' => Yii::t('app', 'Data'),
            'duration' => Yii::t('app', 'Duration'),
            'start_ts' => Yii::t('app', 'Start Ts'),
            'end_ts' => Yii::t('app', 'End Ts'),
            'add_ts' => Yii::t('app', 'Add Ts'),
            'flow_id' => Yii::t('app', 'Flow'),
            'enabled' => Yii::t('app', 'Enabled'),
            'editable' => Yii::t('app', 'Editable'),
        ];
    }

    {
        }

    }

    {
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
}
