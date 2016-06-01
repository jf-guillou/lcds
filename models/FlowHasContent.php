<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "flow_has_content".
 *
 * @property integer $flow_id
 * @property integer $content_id
 *
 * @property Flow $flow
 * @property Content $content
 */
class FlowHasContent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'flow_has_content';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['flow_id', 'content_id'], 'required'],
            [['flow_id', 'content_id'], 'integer'],
            [['flow_id'], 'exist', 'skipOnError' => true, 'targetClass' => Flow::className(), 'targetAttribute' => ['flow_id' => 'id']],
            [['content_id'], 'exist', 'skipOnError' => true, 'targetClass' => Content::className(), 'targetAttribute' => ['content_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'flow_id' => Yii::t('app', 'Flow ID'),
            'content_id' => Yii::t('app', 'Content ID'),
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
    public function getContent()
    {
        return $this->hasOne(Content::className(), ['id' => 'content_id']);
    }
}
