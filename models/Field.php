<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "field".
 *
 * @property integer $id
 * @property integer $type_id
 * @property string $pos
 * @property integer $template_id
 *
 * @property ContentType $type
 * @property ScreenTemplate $template
 */
class Field extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'field';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_id', 'pos', 'template_id'], 'required'],
            [['type_id', 'template_id'], 'integer'],
            [['pos'], 'string'],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContentType::className(), 'targetAttribute' => ['type_id' => 'id']],
            [['template_id'], 'exist', 'skipOnError' => true, 'targetClass' => ScreenTemplate::className(), 'targetAttribute' => ['template_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type_id' => Yii::t('app', 'Type ID'),
            'pos' => Yii::t('app', 'Pos'),
            'template_id' => Yii::t('app', 'Template ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(ContentType::className(), ['id' => 'type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTemplate()
    {
        return $this->hasOne(ScreenTemplate::className(), ['id' => 'template_id']);
    }
}
