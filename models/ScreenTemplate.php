<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "screen_template".
 *
 * @property int $id
 * @property string $name
 * @property int $background_id
 * @property string $css
 * @property Field[] $fields
 * @property Screen[] $screens
 * @property TemplateBackground $background
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
            [['background_id'], 'integer'],
            [['css'], 'string'],
            [['name'], 'string', 'max' => 64],
            [['background'], 'safe'],
            [['background_id'], 'exist', 'skipOnError' => true, 'targetClass' => TemplateBackground::className(), 'targetAttribute' => ['background_id' => 'id']],
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
            'background_id' => Yii::t('app', 'Background'),
            'background' => Yii::t('app', 'Background'),
            'css' => Yii::t('app', 'CSS'),
        ];
    }

    /**
     * After save event
     * Set screen last_modified field on changes to force screen reload.
     *
     * @param bool  $insert            is model inserted
     * @param array $changedAttributes
     */
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
    public function getFieldsArray()
    {
        return $this->hasMany(Field::className(), ['template_id' => 'id'])->with('contentTypes')->asArray();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreens()
    {
        return $this->hasMany(Screen::className(), ['template_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBackground()
    {
        return $this->hasOne(TemplateBackground::className(), ['id' => 'background_id']);
    }

    public function setBackground($backgroundId)
    {
        $this->background_id = $backgroundId;
    }
}
