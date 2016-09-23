<?php

namespace app\models;

use Yii;
use yii\models\Content;

/**
 * This is the model class for table "content_type".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $html
 * @property string $css
 * @property string $js
 * @property string $append_params
 * @property bool $self_update
 * @property string $kind
 * @property string $class_name
 * @property Content[] $contents
 * @property FieldHasContentType[] $fieldHasContentTypes
 * @property Field[] $fields
 */
class ContentType extends \yii\db\ActiveRecord
{
    const KINDS = [
        'RAW' => 'raw',
        'URL' => 'url',
        'FILE' => 'file',
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
            [['description', 'html', 'css', 'js'], 'string'],
            [['self_update'], 'boolean'],
            [['name', 'class_name'], 'string', 'max' => 45],
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
            'description' => Yii::t('app', 'Description'),
            'html' => Yii::t('app', 'Html'),
            'css' => Yii::t('app', 'CSS'),
            'js' => Yii::t('app', 'JS'),
            'append_params' => Yii::t('app', 'Append Params'),
            'self_update' => Yii::t('app', 'Can Update'),
            'kind' => Yii::t('app', 'Kind'),
            'class_name' => Yii::t('app', 'Class Name'),
        ];
    }

    public static function getQuery($selfupdate = null)
    {
        if ($selfupdate === null) {
            return self::find();
        } else {
            return self::find()->where(['self_update' => $selfupdate ? true : false]);
        }
    }

    public static function getAll($selfupdate = null)
    {
        return self::getQuery($selfupdate)->all();
    }

    public static function getAllList($selfupdate = null)
    {
        $types = self::getAll($selfupdate);

        $list = [];

        foreach ($types as $t) {
            $list[$t->id] = Yii::t('app', $t->name);
        }

        return $list;
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
