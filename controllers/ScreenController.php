<?php

namespace app\controllers;

use Yii;
use app\models\Screen;
use app\models\ScreenTemplate;
use app\models\Flow;
use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * ScreenController implements the CRUD actions for Screen model.
 */
class ScreenController extends BaseController
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
                    ['allow' => true, 'actions' => ['index', 'view', 'create', 'update', 'delete', 'link', 'unlink'], 'roles' => ['setScreens']],
                ],
            ],
        ];
    }

    /**
     * Lists all Screen models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Screen::find()->joinWith('template'),
        ]);

        $dataProvider->sort->attributes['template'] = [
            'asc' => [ScreenTemplate::tableName().'.name' => SORT_ASC],
            'desc' => [ScreenTemplate::tableName().'.name' => SORT_DESC],
        ];

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Screen model.
     *
     * @param int $id
     *
     * @return string
     */
    public function actionView($id)
    {
        $model = Screen::find()->where([Screen::tableName().'.id' => $id])->joinWith('template')->one();
        if ($model === null) {
            throw new NotFoundHttpException(Yii::t('app', 'The requested screen does not exist.'));
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $model->getFlows(),
        ]);

        return $this->render('view', [
            'model' => $model,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Screen model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return \yii\web\Reponse|string redirect or render
     */
    public function actionCreate()
    {
        $model = new Screen();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $templates = ScreenTemplate::find()->all();
            $templatesArray = array_reduce($templates, function ($a, $t) {
                $a[$t->id] = $t->name;

                return $a;
            });

            return $this->render('create', [
                'model' => $model,
                'templates' => $templatesArray,
            ]);
        }
    }

    /**
     * Updates an existing Screen model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id
     *
     * @return \yii\web\Reponse|string redirect or render
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $templates = ScreenTemplate::find()->all();
            $templatesArray = array_reduce($templates, function ($a, $t) {
                $a[$t->id] = $t->name;

                return $a;
            });

            return $this->render('update', [
                'model' => $model,
                'templates' => $templatesArray,
            ]);
        }
    }

    /**
     * Deletes an existing Screen model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id
     *
     * @return \yii\web\Reponse
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Adds a flow to this screen or render link view.
     *
     * @param int $id
     * @param int $flowId
     *
     * @return \yii\web\Reponse|string redirect or render
     */
    public function actionLink($id, $flowId = null)
    {
        $model = $this->findModel($id);

        if ($flowId === null) {
            $dataProvider = new ActiveDataProvider([
                'query' => Flow::find()->where(['not', ['id' => ArrayHelper::getColumn($model->flows, 'id')]]),
            ]);

            return $this->render('link', [
                'model' => $model,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            if (!$model->getFlows()->where(['id' => $flowId])->exists()) {
                $model->link('flows', Flow::findOne($flowId));
            }

            return $this->redirect(['view', 'id' => $id]);
        }
    }

    /**
     * Remove a flow from a screen.
     *
     * @param int $id
     * @param int $flowId
     *
     * @return \yii\web\Reponse
     */
    public function actionUnlink($id, $flowId)
    {
        $model = $this->findModel($id);

        if ($model->getFlows()->where(['id' => $flowId])->exists()) {
            $model->unlink('flows', Flow::findOne($flowId), true);
        }

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Finds the Screen model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     *
     * @return Screen the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Screen::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested screen does not exist.'));
        }
    }
}
