<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "field".
 *
 * @property int $id
 * @property int $template_id
 * @property float $x1
 * @property float $y1
 * @property float $x2
 * @property float $y2
 * @property string $css
 * @property string $js
 * @property ScreenTemplate $template
 * @property FieldHasContentType[] $fieldHasContentTypes
 * @property ContentType[] $contentTypes
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
            [['template_id', 'x1', 'y1', 'x2', 'y2', 'random_order'], 'required'],
            [['template_id'], 'integer'],
            [['x1', 'y1', 'x2', 'y2'], 'number'],
            [['css', 'js'], 'string'],
            [['random_order'], 'boolean'],
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
            'template_id' => Yii::t('app', 'Template ID'),
            'x1' => Yii::t('app', 'X1'),
            'y1' => Yii::t('app', 'Y1'),
            'x2' => Yii::t('app', 'X2'),
            'y2' => Yii::t('app', 'Y2'),
            'css' => Yii::t('app', 'CSS'),
            'js' => Yii::t('app', 'JS'),
            'random_order' => Yii::t('app', 'Random order'),
            'contentTypes' => Yii::t('app', 'Content types'),
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
        foreach ($this->template->screens as $screen) {
            $screen->setModified();
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTemplate()
    {
        return $this->hasOne(ScreenTemplate::className(), ['id' => 'template_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFieldHasContentTypes()
    {
        return $this->hasMany(FieldHasContentType::className(), ['field_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentTypes()
    {
        return $this->hasMany(ContentType::className(), ['id' => 'content_type_id'])->viaTable('field_has_content_type', ['field_id' => 'id']);
    }

    /**
     * Merge data from content type with field data.
     *
     * @param string $data content type data
     *
     * @return string transformed data
     */
    public function mergeData($data)
    {
        return str_replace([
            '%x1%',
            '%x2%',
            '%y1%',
            '%y2%',
        ], [
            $this->x1,
            $this->x2,
            $this->y1,
            $this->y2,
        ], $data);
    }
}
