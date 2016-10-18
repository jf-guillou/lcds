<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "content_type".
 *
 * @property string $id Class name
 * @property Content[] $contents
 * @property FieldHasContentType[] $fieldHasContentTypes
 * @property Field[] $fields
 */
class ContentType extends \yii\db\ActiveRecord
{
    public $_name;
    public $_description;
    public $html;
    public $css;
    public $js;
    public $appendParams;
    public $selfUpdate;
    public $input;
    public $output;
    public $usable;
    public static $typeAttributes = [
        'typeName' => '_name',
        'typeDescription' => '_description',
        'html' => 'html',
        'css' => 'css',
        'js' => 'js',
        'appendParams' => 'appendParams',
        'selfUpdate' => 'selfUpdate',
        'input' => 'input',
        'output' => 'output',
        'usable' => 'usable',
    ];

    const KINDS = [
        'NONE' => 'none',
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
            [['id'], 'required'],
            [['id'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Type'),
        ];
    }

    /**
     * Overload default instantiate to fill attributes from specific content type.
     *
     * @param array $row
     *
     * @return array transformed row
     */
    public static function instantiate($row)
    {
        $t = new static();
        $c = Content::fromType($row['id']);
        foreach ($t::$typeAttributes as $cAtt => $tAtt) {
            if ($c::$$cAtt !== null) {
                $t->$tAtt = $c::$$cAtt;
            }
        }

        return $t;
    }

    /**
     * Get all filtered content types.
     *
     * @param bool $selfUpdate does content type manages itself
     * @param bool $usableOnly show only usable content types
     *
     * @return array content types
     */
    public static function getAll($selfUpdate = null, $usableOnly = true)
    {
        $types = self::find()->all();

        return array_filter($types, function ($t) use ($selfUpdate, $usableOnly) {
            return ($selfUpdate === null || $t->selfUpdate == $selfUpdate) && (!$usableOnly || $t->usable);
        });
    }

    /**
     * Get all filterd content types in array.
     *
     * @param bool $selfUpdate does content type manages itself
     * @param bool $usableOnly show only usable content types
     *
     * @return array content types
     */
    public static function getAllList($selfUpdate = null, $usableOnly = true)
    {
        $types = self::getAll($selfUpdate, $usableOnly);

        $list = [];

        foreach ($types as $t) {
            $list[$t->id] = $t->name;
        }

        return $list;
    }

    /**
     * Get all file based content types.
     *
     * @return array content types
     */
    public static function getAllFileTypeIds()
    {
        $types = self::find()->all();

        return array_filter(array_map(function ($t) {
            return $t->input == self::KINDS['FILE'] ? $t->id : null;
        }, $types));
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

    /**
     * Get translated content type name.
     *
     * @return string name
     */
    public function getName()
    {
        return \Yii::t('app', $this->_name);
    }

    /**
     * Get translated content type description.
     *
     * @return string description
     */
    public function getDescription()
    {
        return \Yii::t('app', $this->_description);
    }
}
