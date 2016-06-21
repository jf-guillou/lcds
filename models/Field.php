<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "field".
 *
 * @property int $id
 * @property int $type_id
 * @property int $template_id
 * @property float $x1
 * @property float $y1
 * @property float $x2
 * @property float $y2
 * @property string $css
 * @property string $js
 * @property ContentType $type
 * @property ScreenTemplate $template
 */
class Field extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'field';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type_id', 'template_id', 'x1', 'y1', 'x2', 'y2'], 'required'],
            [['type_id', 'template_id'], 'integer'],
            [['x1', 'y1', 'x2', 'y2'], 'number'],
            [['css', 'js'], 'string'],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContentType::className(), 'targetAttribute' => ['type_id' => 'id']],
            [['template_id'], 'exist', 'skipOnError' => true, 'targetClass' => ScreenTemplate::className(), 'targetAttribute' => ['template_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type_id' => Yii::t('app', 'Type ID'),
            'template_id' => Yii::t('app', 'Template ID'),
            'x1' => Yii::t('app', 'X1'),
            'y1' => Yii::t('app', 'Y1'),
            'x2' => Yii::t('app', 'X2'),
            'y2' => Yii::t('app', 'Y2'),
            'css' => Yii::t('app', 'Css'),
            'js' => Yii::t('app', 'Js'),
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
