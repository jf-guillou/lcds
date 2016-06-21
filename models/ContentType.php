<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "content_type".
 *
 * @property int $id
 * @property string $name
 * @property string $html
 * @property string $css
 * @property string $js
 * @property bool $can_update
 * @property bool $is_string
 * @property string $attribute
 * @property Content[] $contents
 * @property Field[] $fields
 */
class ContentType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'content_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['html', 'css', 'js'], 'string'],
            [['can_update', 'is_string'], 'boolean'],
            [['name', 'attribute'], 'string', 'max' => 45],
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
            'html' => Yii::t('app', 'Html'),
            'css' => Yii::t('app', 'Css'),
            'js' => Yii::t('app', 'Js'),
            'can_update' => Yii::t('app', 'Can Update'),
            'is_string' => Yii::t('app', 'Is String'),
            'attribute' => Yii::t('app', 'Attribute'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContents()
    {
        return $this->hasMany(Content::className(), ['type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFields()
    {
        return $this->hasMany(Field::className(), ['type_id' => 'id']);
    }
}
