<?php

namespace app\models;

use Yii;
use app\models\types\Media;

/**
 * This is the model class for template background upload.
 *
 * @property file $background
 */
class TemplateBackgroundUpload extends \yii\base\Model
{
    const PATH = 'background/';
    public $background;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['background'], 'image', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, gif'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'background' => Yii::t('app', 'Background upload'),
        ];
    }

    public function upload($fileInstance)
    {
        $this->background = $fileInstance;
        $name = $this->background->baseName.'.'.$this->background->extension;
        $path = Media::BASE_PATH.self::PATH;
        $filepath = $path.$name;
        if ($this->validate() && $this->background->saveAs($filepath)) {
            $model = new TemplateBackground();
            $model->webpath = Media::BASE_URI.$filepath;

            return $model->save();
        }

        return false;
    }
}
