<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "content".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property integer $type_id
 * @property string $data
 * @property integer $duration
 * @property string $start_ts
 * @property string $end_ts
 * @property string $add_ts
 * @property boolean $enabled
 * @property integer $owner_id
 *
 * @property ContentType $type
 * @property FlowHasContent[] $flowHasContents
 * @property Flow[] $flows
 */
class Content extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'content';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type_id', 'owner_id'], 'required'],
            [['type_id', 'duration', 'owner_id'], 'integer'],
            [['data'], 'string'],
            [['start_ts', 'end_ts', 'add_ts'], 'safe'],
            [['enabled'], 'boolean'],
            [['name'], 'string', 'max' => 64],
            [['description'], 'string', 'max' => 1024],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContentType::className(), 'targetAttribute' => ['type_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
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
            'enabled' => Yii::t('app', 'Enabled'),
            'owner_id' => Yii::t('app', 'Owner ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(ContentType::className(), ['id' => 'type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFlowHasContents()
    {
        return $this->hasMany(FlowHasContent::className(), ['content_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFlows()
    {
        return $this->hasMany(Flow::className(), ['id' => 'flow_id'])->viaTable('flow_has_content', ['content_id' => 'id']);
    }
}
