<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "flow".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $owner_group
 * @property int $parent_id
 * @property Content[] $contents
 * @property Flow $parent
 * @property Flow[] $flows
 * @property ScreenHasFlow[] $screenHasFlows
 * @property Screen[] $screens
 */
class Flow extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'flow';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'owner_group'], 'required'],
            [['parent_id'], 'integer'],
            [['name', 'owner_group'], 'string', 'max' => 64],
            [['description'], 'string', 'max' => 1024],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => self::className(), 'targetAttribute' => ['parent_id' => 'id']],
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
            'owner_group' => Yii::t('app', 'Owner'),
            'parent_id' => Yii::t('app', 'Parent'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContents()
    {
        return $this->hasMany(Content::className(), ['flow_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(self::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(self::className(), ['parent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreenHasFlows()
    {
        return $this->hasMany(ScreenHasFlow::className(), ['flow_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreens()
    {
        return $this->hasMany(Screen::className(), ['id' => 'screen_id'])->viaTable('screen_has_flow', ['flow_id' => 'id']);
    }
}
