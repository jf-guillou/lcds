<?php

namespace app\controllers;

use Yii;
use app\models\TemplateBackground;
use app\models\TemplateBackgroundUpload;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;

/**
 * TemplateBackgroundController implements the CRUD actions for TemplateBackground model.
 */
class TemplateBackgroundController extends Controller
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
     * Lists all TemplateBackground models.
     *
     * @return string render
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => TemplateBackground::find(),
        ]);

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('index', [
                'dataProvider' => $dataProvider,
            ]);
        }

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new TemplateBackground model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return \yii\web\Response|string redirect or render
     */
    public function actionCreate()
    {
        $modelUpload = new TemplateBackgroundUpload();
        if ($modelUpload->load(Yii::$app->request->post()) && $modelUpload->upload(UploadedFile::getInstance($modelUpload, 'background'))) {
            if (Yii::$app->request->isAjax) {
                return '';
            }

            return $this->redirect(['index']);
        }

        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('create', [
                'model' => $modelUpload,
            ]);
        }

        return $this->render('create', [
            'model' => $modelUpload,
        ]);
    }

    /**
     * Deletes an existing TemplateBackground model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id
     *
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        if (Yii::$app->request->isAjax) {
            return '';
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the TemplateBackground model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     *
     * @return TemplateBackground the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TemplateBackground::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested template background does not exist.'));
        }
    }
}
