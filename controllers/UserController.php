<?php

namespace app\controllers;

use Yii;
use app\models\User;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'view', 'create', 'import', 'delete', 'set-role'],
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'view', 'create', 'delete', 'set-role'], 'roles' => ['admin']],
                    ['allow' => true, 'actions' => ['import'], 'matchCallback' => function ($rule, $action) {
                        return Yii::$app->params['useLdap'];
                    }],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->username]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    public function actionImport()
    {
        $model = new User();

        if ($model->load(Yii::$app->request->post()) && $model->validate(['username'])) {
            if ($model->findInLdap()) {
                $model->save(false);

                return $this->redirect(['view', 'id' => $model->username]);
            } else {
                $model->addError('username', Yii::t('app', 'This user doesn\'t exist in LDAP'));
            }
        }

        return $this->render('import', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param string $id
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param string $id
     *
     * @return User the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionLanguage($language)
    {
        Yii::$app->session->set('language', $language);
        Yii::$app->response->cookies->add(new \yii\web\Cookie([
            'name' => 'language',
            'value' => $language,
        ]));
        if (!Yii::$app->user->isGuest) {
            Yii::$app->user->identity->setLanguage($language);
        }

        return $this->goBack();
    }

    public function actionSetRole($username, $roleName)
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($roleName);
        if ($role) {
            $auth->assign($role, $username);
        }

        return $this->goBack();
    }
    public function actionSetRole2($username, $roleName)
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($roleName);
        if ($role) {
            $auth->assign($role, $username);
        }

        return $this->goBack();
    }

    public function actionRoles($username)
    {
        $auth = Yii::$app->authManager;

        var_dump($auth->getRolesByUser($username));
    }
}
