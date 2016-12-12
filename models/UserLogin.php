<?php

namespace app\models;

use Yii;

/**
 * This is the model class for login form.
 *
 * @property string $username
 * @property string $password
 * @property bool $remember_me
 */
class UserLogin extends \yii\base\Model
{
    public $username;
    public $password;
    public $remember_me = false;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['username', 'password'], 'string', 'max' => 64],
            [['remember_me'], 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'Username'),
            'password' => Yii::t('app', 'Password'),
            'remember_me' => Yii::t('app', 'Remember me'),
        ];
    }

    /**
     * Override action to disable default save
     * This model has no true support table and is only used for display and validation purposes.
     *
     * @return bool success
     */
    public function save()
    {
        return false;
    }
}
