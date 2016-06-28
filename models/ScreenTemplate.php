<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "screen_template".
 *
 * @property int $id
 * @property string $name
 * @property string $background
 * @property Field[] $fields
 * @property Screen[] $screens
 */
class ScreenTemplate extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'screen_template';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 64],
            [['background'], 'string', 'max' => 256],
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
            'background' => Yii::t('app', 'Background'),
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        foreach ($this->screens as $screen) {
            $screen->setModified();
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFields()
    {
        return $this->hasMany(Field::className(), ['template_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreens()
    {
        return $this->hasMany(Screen::className(), ['template_id' => 'id']);
    }
}
