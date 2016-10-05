<?php

namespace app\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "user".
 *
 * @property string $username
 * @property string $password
 * @property string $hash
 * @property string $language
 * @property string $authkey
 * @property string $access_token
 * @property string $added_at
 * @property string $last_login_at
 * @property bool $fromLdap
 * @property bool $remember_me
 * @property UserHasFlow[] $userHasFlows
 * @property Flow[] $flows
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    public $password;
    public $fromLdap = false;
    public $remember_me = false;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['added_at', 'last_login_at'], 'safe'],
            [['language'], 'string', 'max' => 8],
            [['username', 'password', 'hash', 'authkey', 'access_token'], 'string', 'max' => 64],
            [['remember_me'], 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'Username'),
            'hash' => Yii::t('app', 'Hash'),
            'password' => Yii::t('app', 'Password'),
            'language' => Yii::t('app', 'Language'),
            'authkey' => Yii::t('app', 'Authkey'),
            'access_token' => Yii::t('app', 'Access token'),
            'added_at' => Yii::t('app', 'Added at'),
            'last_login_at' => Yii::t('app', 'Last login at'),
            'remember_me' => Yii::t('app', 'Remember me'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authkey;
    }

    public function authenticate($password)
    {
        if (Yii::$app->params['useLdap'] && Yii::$app->ldap->authenticate($this->getId(), $password)) {
            $this->fromLdap = true;

            return true;
        }

        return $this->validatePassword($password);
    }

    public function setLastLogin()
    {
        $this->last_login_at = new Expression('NOW()');
        $this->save();
    }

    public function initFromLDAP()
    {
        if (Yii::$app->params['useLdap'] && Yii::$app->ldap->authenticate($this->getId(), $this->password)) {
            $this->fromLdap = true;
            $this->save();

            return $this;
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthkey() === $authkey;
    }

    /**
     * Validates password.
     *
     * @param string $password password to validate
     *
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->hash);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->language = Yii::$app->params['language'];
                $this->authkey = Yii::$app->security->generateRandomString();
                $this->access_token = Yii::$app->security->generateRandomString();
                if (!$this->fromLdap) {
                    $this->hash = Yii::$app->security->generatePasswordHash($this->password);
                    $this->password = null;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserHasFlows()
    {
        return $this->hasMany(UserHasFlow::className(), ['user_username' => 'username']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFlows()
    {
        return $this->hasMany(Flow::className(), ['id' => 'flow_id'])->viaTable('user_has_flow', ['user_username' => 'username']);
    }
}
