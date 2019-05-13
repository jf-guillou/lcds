<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "screen_has_flow".
 *
 * @property integer $screen_id
 * @property integer $flow_id
 *
 * @property Screen $screen
 * @property Flow $flow
 */
class ScreenHasFlow extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'screen_has_flow';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['screen_id', 'flow_id'], 'required'],
            [['screen_id', 'flow_id'], 'integer'],
            [['screen_id'], 'exist', 'skipOnError' => true, 'targetClass' => Screen::class, 'targetAttribute' => ['screen_id' => 'id']],
            [['flow_id'], 'exist', 'skipOnError' => true, 'targetClass' => Flow::class, 'targetAttribute' => ['flow_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'screen_id' => Yii::t('app', 'Screen ID'),
            'flow_id' => Yii::t('app', 'Flow ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreen()
    {
        return $this->hasOne(Screen::class, ['id' => 'screen_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFlow()
    {
        return $this->hasOne(Flow::class, ['id' => 'flow_id']);
    }
}
