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
            [['remember_me'], 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'Username'),
            'password' => Yii::t('app', 'Password'),
            'remember_me' => Yii::t('app', 'Remember me'),
        ];
    }

    public function save()
    {
        return false;
    }
}
