<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use app\models\User;
use app\models\UserLogin;

/**
 * ContentController implements the CRUD actions for Content model.
 */
class AuthController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['login', 'logout'],
                'rules' => [
                    ['allow' => true, 'actions' => ['login'], 'roles' => ['?']],
                    ['allow' => true, 'actions' => ['logout'], 'roles' => ['@']],
                ],
            ],
        ];
    }

    public function init()
    {
        parent::init();

        Yii::$app->user->on(\yii\web\User::EVENT_AFTER_LOGIN, ['app\models\User', 'afterLogin']);
    }

    public function actionIndex()
    {
        return $this->actionLogin();
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goBack();
        }

        if (Yii::$app->params['useKerberos'] && isset($_SERVER[Yii::$app->params['kerberosPrincipalVar']])) {
            $username = $_SERVER[Yii::$app->params['kerberosPrincipalVar']];

            $identity = User::findIdentity($username);
            if ($identity) {
                Yii::$app->user->login($identity, Yii::$app->params['cookieDuration']);

                return $this->goBack();
            }
        }

        $model = new UserLogin();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // Try to login
            $identity = User::findIdentity($model->username);
            if ($identity !== null && $identity->authenticate($model->password)) {
                Yii::$app->user->enableAutoLogin = $model->remember_me;
                Yii::$app->user->login($identity, Yii::$app->params['cookieDuration']);

                return $this->goBack();
            }
            $model->addError('username', Yii::t('app', 'Username or password incorrect'));
        }

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        if (Yii::$app->user->isGuest) {
            return $this->goBack();
        }

        Yii::$app->user->logout();

        return $this->goHome();
    }
}
