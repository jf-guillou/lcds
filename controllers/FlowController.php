<?php

namespace app\controllers;

use Yii;
use app\models\Flow;
use app\models\Content;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * FlowController implements the CRUD actions for Flow model.
 */
class FlowController extends BaseController
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
                'only' => ['index', 'view', 'create', 'update', 'delete'],
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'view'], 'roles' => ['@']],
                    ['allow' => true, 'actions' => ['create', 'update', 'delete'], 'roles' => ['setFlows']],
                ],
            ],
        ];
    }

    /**
     * Lists all Flow models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $query = Flow::availableQuery(Yii::$app->user);
        if ($query === null) {
            throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'You do not have enough rights to view this flow.'));
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Flow model.
     *
     * @param int $id
     *
     * @return string
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if (!$model->canView(Yii::$app->user)) {
            throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'You do not have enough rights to view this flow.'));
        }

        $dataProvider = new ActiveDataProvider([
            'query' => Content::find()->joinWith(['flow'])->where([Flow::tableName().'.id' => $id]),
        ]);

        return $this->render('view', [
            'model' => $this->findModel($id),
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Flow model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return \yii\web\Reponse|string redirect or render
     */
    public function actionCreate()
    {
        $model = new Flow();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $flows = ArrayHelper::map(Flow::find()->all(), 'id', 'name');

            return $this->render('create', [
                'model' => $model,
                'flows' => ['' => Yii::t('app', '(none)')] + $flows,
            ]);
        }
    }

    /**
     * Updates an existing Flow model.
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
            $flows = ArrayHelper::map(Flow::find()->all(), 'id', 'name');

            return $this->render('update', [
                'model' => $model,
                'flows' => ['' => Yii::t('app', '(none)')] + $flows,
            ]);
        }
    }

    /**
     * Deletes an existing Flow model.
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
     * Finds the Flow model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     *
     * @return Flow the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Flow::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested flow does not exist.'));
        }
    }
}
