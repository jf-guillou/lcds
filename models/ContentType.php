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
 * @property bool $self_update
 * @property string $append_params
 * @property string $kind
 * @property Content[] $contents
 * @property FieldHasContentType[] $fieldHasContentTypes
 * @property Field[] $fields
 */
class ContentType extends \yii\db\ActiveRecord
{
    const KINDS = [
        'RAW' => 'raw',
        'URL' => 'url',
        'TEXT' => 'text',
    ];

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
            [['self_update'], 'boolean'],
            [['name'], 'string', 'max' => 45],
            [['append_params'], 'string', 'max' => 1024],
            [['kind'], 'in', 'range' => self::KINDS],
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
            'self_update' => Yii::t('app', 'Can Update'),
            'append_params' => Yii::t('app', 'Append Params'),
            'kind' => Yii::t('app', 'Kind'),
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
    public function getFieldHasContentTypes()
    {
        return $this->hasMany(FieldHasContentType::className(), ['content_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFields()
    {
        return $this->hasMany(Field::className(), ['id' => 'field_id'])->viaTable('field_has_content_type', ['content_type_id' => 'id']);
    }
}
