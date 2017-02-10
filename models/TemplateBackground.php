<?php

namespace app\models;

use Yii;
use yii\helpers\Url;
use app\models\types\Media;

/**
 * This is the model class for table "template_background".
 *
 * @property int $id
 * @property string $webpath
 * @property ScreenTemplate[] $screenTemplates
 */
class TemplateBackground extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'template_background';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['webpath'], 'required'],
            [['webpath'], 'string', 'max' => 256],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'webpath' => Yii::t('app', 'Filepath'),
            'name' => Yii::t('app', 'Background'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        if ($this->shouldDeleteFile()) {
            unlink($this->getRealFilepath());
        }
        parent::afterDelete();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreenTemplates()
    {
        return $this->hasMany(ScreenTemplate::className(), ['background_id' => 'id']);
    }

    /**
     * Returns Yii usable URL for this background.
     *
     * @return string url
     */
    public function getUri()
    {
        return Url::to($this->webpath);
    }

    /**
     * Search background name in file webpath.
     *
     * @return string name
     */
    public function getName()
    {
        $parts = explode('/', $this->webpath);

        return $parts[count($parts) - 1];
    }

    /**
     * Count templatesBackghround currently using this background to decide on file deletion.
     *
     * @return bool
     */
    public function shouldDeleteFile()
    {
        return self::find()->where(['webpath' => $this->webpath])->count() < 1;
    }

    /**
     * Extract real filepath from background webpath.
     *
     * @return string file path
     */
    public function getRealFilepath()
    {
        return str_replace(Media::BASE_URI, '', $this->webpath);
    }
}
