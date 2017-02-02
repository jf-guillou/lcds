<?php

namespace app\controllers;

use Yii;
use app\models\User;
use app\models\UserLogin;
use app\models\Flow;
use yii\helpers\ArrayHelper;
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
                'only' => ['index', 'view', 'create', 'import', 'delete', 'set-roles'],
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'view', 'create', 'delete', 'set-roles'], 'roles' => ['admin']],
                    ['allow' => true, 'actions' => ['import'], 'matchCallback' => function () {
                        return Yii::$app->params['useLdap'];
                    }],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     *
     * @return string render
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
     * @return string render
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
     * @return \yii\web\Response|string redirect or render
     */
    public function actionCreate()
    {
        $model = new UserLogin();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if (!User::findIdentity($model->username)) {
                if (($user = User::create($model->username, $model->password)) !== null) {
                    return $this->redirect(['view', 'id' => $user->getId()]);
                } else {
                    $model->addError('username', Yii::t('app', 'User creation failed'));
                }
            } else {
                $model->addError('username', Yii::t('app', 'This user is already registered'));
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Import user from LDAP and save it.
     *
     * @return \yii\web\Response|string redirect or render
     */
    public function actionImport()
    {
        $model = new UserLogin();

        if ($model->load(Yii::$app->request->post()) && $model->validate(['username'])) {
            if (!User::findOne($model->username)) {
                if (($user = User::findInLdap($model->username)) !== null) {
                    $user->save(false);

                    return $this->redirect(['view', 'id' => $user->username]);
                } else {
                    $model->addError('username', Yii::t('app', 'This user doesn\'t exist in LDAP'));
                }
            } else {
                $model->addError('username', Yii::t('app', 'This user is already registered'));
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
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Set roles and flows for a specific user.
     *
     * @param string $id user id
     *
     * @return \yii\web\Response|string redirect or render
     */
    public function actionSetRoles($id)
    {
        $model = $this->findModel($id);
        $auth = Yii::$app->authManager;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->username]);
        }

        $roles = $auth->getRoles();
        $rolesArray = ['' => Yii::t('app', 'None')];
        $flowableRoles = [];
        foreach ($roles as $name => $role) {
            $rolesArray[$name] = Yii::t('app', $name);
            if ($role->data && array_key_exists('requireFlow', $role->data)) {
                $flowableRoles[] = $name;
            }
        }

        $flows = ArrayHelper::map(Flow::find()->all(), 'id', 'name');

        return $this->render('set-roles', [
            'model' => $model,
            'roles' => $rolesArray,
            'flows' => $flows,
            'flowableRoles' => $flowableRoles,
        ]);
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
            throw new NotFoundHttpException(Yii::t('app', 'The requested user does not exist.'));
        }
    }
}
