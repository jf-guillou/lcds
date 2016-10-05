<?php

namespace app\controllers;

use Yii;
use app\models\Screen;
use app\models\ScreenTemplate;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

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
        ];
    }

    /**
     * Lists all Screen models.
     *
     * @return mixed
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
     * @return mixed
     */
    public function actionView($id)
    {
        $model = Screen::find()->where([Screen::tableName().'.id' => $id])->joinWith('template')->one();
        if ($model === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Screen model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
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
     * @return mixed
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
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
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
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
