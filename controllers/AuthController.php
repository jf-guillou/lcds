<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use app\models\User;

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

    public function actionIndex()
    {
        return $this->actionLogin();
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goBack();
        }

        if (isset($_SERVER['REDIRECT_REMOTE_USER'])) {
            $username = $_SERVER['REDIRECT_REMOTE_USER'];

            $identity = User::findIdentity($username);
            if ($identity) {
                Yii::$app->user->login($identity, Yii::$app->params['cookieDuration']);

                return $this->goBack();
            }
        }

        $model = new User();
        if ($model->load(Yii::$app->request->post())) {
            // Try to login
            $identity = User::findIdentity($model->getId());
            if ($identity || ($identity = $model->initFromLDAP()) !== null) {
                if ($identity->authenticate($model->password)) {
                    Yii::$app->user->enableAutoLogin = $model->remember_me;
                    Yii::$app->user->login($identity, Yii::$app->params['cookieDuration']);

                    return $this->goBack();
                }
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
