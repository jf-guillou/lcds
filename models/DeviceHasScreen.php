<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "device_has_screen".
 *
 * @property int $device_id
 * @property int $screen_id
 * @property Device $device
 * @property Screen $screen
 */
class DeviceHasScreen extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'device_has_screen';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['device_id', 'screen_id'], 'required'],
            [['device_id', 'screen_id'], 'integer'],
            [['device_id'], 'exist', 'skipOnError' => true, 'targetClass' => Device::className(), 'targetAttribute' => ['device_id' => 'id']],
            [['screen_id'], 'exist', 'skipOnError' => true, 'targetClass' => Screen::className(), 'targetAttribute' => ['screen_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'device_id' => Yii::t('app', 'Device ID'),
            'screen_id' => Yii::t('app', 'Screen ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDevice()
    {
        return $this->hasOne(Device::className(), ['id' => 'device_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreen()
    {
        return $this->hasOne(Screen::className(), ['id' => 'screen_id']);
    }
}
