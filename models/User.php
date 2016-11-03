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
            [['username'], 'required'],
            [['added_at', 'last_login_at', 'roleName', 'flows'], 'safe'],
            [['username', 'hash', 'authkey', 'access_token'], 'string', 'max' => 64],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'Username'),
            'hash' => Yii::t('app', 'Hash'),
            'authkey' => Yii::t('app', 'Authkey'),
            'access_token' => Yii::t('app', 'Access token'),
            'added_at' => Yii::t('app', 'Added at'),
            'last_login_at' => Yii::t('app', 'Last login at'),
            'roleName' => Yii::t('app', 'Role'),
            'flows' => Yii::t('app', 'Flows'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        $user = static::findOne($id);
        if (!$user) {
            $user = self::findInLdap($id);
        }

        return $user;
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

    /**
     * Create a used with specified username and password.
     *
     * @param string $username
     * @param string $password
     *
     * @return \User|null created user
     */
    public static function create($username, $password)
    {
        $user = new self();
        $user->username = $username;
        $user->password = $password;

        return $user->save() ? $user : null;
    }

    /**
     * Authenticate user with password on LDAP is possible or in DB.
     *
     * @param string $password
     *
     * @return bool is authenticated
     */
    public function authenticate($password)
    {
        $this->password = $password;
        if (Yii::$app->params['useLdap'] && Yii::$app->ldap->authenticate($this->getId(), $password)) {
            $this->fromLdap = true;

            return true;
        }

        return $this->validatePassword($password);
    }

    /**
     * Look for a user ID in LDAP.
     *
     * @param string $id user ID
     *
     * @return \User|null found user
     */
    public static function findInLdap($id)
    {
        if (Yii::$app->params['useLdap']) {
            $ldapUser = Yii::$app->ldap->users()->find($id);
            if ($ldapUser) {
                $user = new self();
                $user->username = $id;
                $user->fromLdap = true;

                return $user;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authkey)
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
        if (!$this->hash) {
            return false;
        }

        return Yii::$app->security->validatePassword($password, $this->hash);
    }

    /**
     * After find event
     * Update user to indicated LDAP or DB source.
     */
    public function afterFind()
    {
        parent::afterFind();
        if ($this->hash === null) {
            $this->fromLdap = true;
        }
    }

    /**
     * Before save event
     * Create authkey and access token is newly created
     * Also hash password if not comming from LDAP.
     *
     * @param bool $insert is model inserted
     *
     * @return bool success
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->authkey = Yii::$app->security->generateRandomString();
                $this->access_token = Yii::$app->security->generateRandomString();
                if (!$this->fromLdap && $this->password) {
                    $this->hash = Yii::$app->security->generatePasswordHash($this->password);
                    $this->password = null;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * After login event
     * Update last_login_at field to indicate login timestamp.
     *
     * @param mixed $event user event
     */
    public static function afterLogin($event)
    {
        $event->identity->last_login_at = new Expression('NOW()');

        $event->identity->save();
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

    /**
     * Update usable flows for user from form
     * Link and unlink based on new & old flow id arrays.
     *
     * @param array $flows flows ids
     */
    public function setFlows($flows)
    {
        if ($flows === '' || count($flows) === 0) {
            $this->unlinkAll('flows', true);

            return true;
        }

        $prevFlows = array_map(function ($f) {
            return $f->id;
        }, $this->flows);

        $unlink = array_diff($prevFlows, $flows);
        if (count($unlink)) {
            $uFlows = Flow::findAll($unlink);
            foreach ($uFlows as $f) {
                $this->unlink('flows', $f, true);
            }
        }

        $link = array_diff($flows, $prevFlows);
        if (count($link)) {
            $lFlows = Flow::findAll($link);
            foreach ($lFlows as $f) {
                $this->link('flows', $f);
            }
        }

        return true;
    }

    /**
     * Retrieve user role from authmanager.
     *
     * @return \yii\rbac\Role|null first role
     */
    public function getRole()
    {
        $roles = Yii::$app->authManager->getRolesByUser($this->getId());
        $roleNames = array_keys($roles);

        return count($roleNames) ? $roles[$roleNames[0]] : null;
    }

    /**
     * Retrieve user role from authmanager.
     *
     * @return string|null first role name
     */
    public function getRoleName()
    {
        $role = $this->role;

        return $role ? $role->name : null;
    }

    /**
     * Set user role in authmanager.
     *
     * @param string $roleName
     *
     * @return bool success
     */
    public function setRoleName($roleName)
    {
        $auth = Yii::$app->authManager;
        $auth->revokeAll($this->getId());
        if (($r = $auth->getRole($roleName)) !== null) {
            return $auth->assign($r, $this->getId()) !== null;
        }

        return false;
    }

    /**
     * Indicates if we have to display flows with associated role.
     *
     * @return bool need to display flow
     */
    public function getNeedsFlow()
    {
        $role = $this->role;

        return $role && $role->data && array_key_exists('requireFlow', $role->data);
    }
}
