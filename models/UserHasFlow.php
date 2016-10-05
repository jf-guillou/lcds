<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_has_flow".
 *
 * @property string $user_username
 * @property int $flow_id
 * @property User $userUsername
 * @property Flow $flow
 */
class UserHasFlow extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_has_flow';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_username', 'flow_id'], 'required'],
            [['flow_id'], 'integer'],
            [['user_username'], 'string', 'max' => 64],
            [['user_username'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_username' => 'username']],
            [['flow_id'], 'exist', 'skipOnError' => true, 'targetClass' => Flow::className(), 'targetAttribute' => ['flow_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_username' => Yii::t('app', 'User Username'),
            'flow_id' => Yii::t('app', 'Flow ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['username' => 'user_username']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFlow()
    {
        return $this->hasOne(Flow::className(), ['id' => 'flow_id']);
    }
}
