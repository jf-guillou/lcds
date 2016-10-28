<?php

namespace app\controllers;

use Yii;
use app\models\Device;
use app\models\Screen;
use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * ScreenController implements the CRUD actions for Device model.
 */
class DeviceController extends BaseController
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
                'only' => ['index', 'view', 'create', 'update', 'delete', 'link', 'unlink'],
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'view', 'create', 'update', 'delete', 'link', 'unlink'], 'roles' => ['setDevices']],
                ],
            ],
        ];
    }

    /**
     * Lists all Device models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Device::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Device model.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $dataProvider = new ActiveDataProvider([
            'query' => $model->getScreens(),
        ]);

        return $this->render('view', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Device model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Device();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Device model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Device model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Adds a Screen to this Device or render link view.
     *
     * @param int $id
     * @param int $screenId
     *
     * @return mixed
     */
    public function actionLink($id, $screenId = null)
    {
        $model = $this->findModel($id);

        if ($screenId === null) {
            $dataProvider = new ActiveDataProvider([
                'query' => Screen::find()->where(['not', ['id' => ArrayHelper::getColumn($model->screens, 'id')]]),
            ]);

            return $this->render('link', [
                'model' => $model,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            if (!$model->getScreens()->where(['id' => $screenId])->exists()) {
                $model->link('screens', Screen::findOne($screenId));
            }

            return $this->redirect(['view', 'id' => $id]);
        }
    }

    /**
     * Remove a Screen from a Device.
     *
     * @param int $id
     * @param int $flowId
     *
     * @return mixed
     */
    public function actionUnlink($id, $screenId)
    {
        $model = $this->findModel($id);

        if ($model->getScreens()->where(['id' => $screenId])->exists()) {
            $model->unlink('flows', Screen::findOne($screenId), true);
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Enables or disables a Device.
     *
     * @param int $id screen id
     *
     * @return mixed
     */
    public function actionToggle($id)
    {
        $model = $this->findModel($id);

        $model->enabled = !$model->enabled;
        $model->save();

        return $this->smartGoBack();
    }

    /**
     * Finds the Device model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     *
     * @return Device the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Device::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
