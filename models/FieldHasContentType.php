<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "field_has_content_type".
 *
 * @property int $field_id
 * @property string $content_type_id
 * @property Field $field
 * @property ContentType $contentType
 */
class FieldHasContentType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'field_has_content_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['field_id', 'content_type_id'], 'required'],
            [['field_id'], 'integer'],
            [['content_type_id'], 'string', 'max' => 45],
            [['field_id'], 'exist', 'skipOnError' => true, 'targetClass' => Field::class, 'targetAttribute' => ['field_id' => 'id']],
            [['content_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContentType::class, 'targetAttribute' => ['content_type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'field_id' => Yii::t('app', 'Field ID'),
            'content_type_id' => Yii::t('app', 'Content Type'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getField()
    {
        return $this->hasOne(Field::class, ['id' => 'field_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentType()
    {
        return $this->hasOne(ContentType::class, ['id' => 'content_type_id']);
    }
}
