<?php

namespace app\controllers;

use Yii;
use app\models\ContentType;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * ContentController implements the CRUD actions for ContentType model.
 */
class ContentTypeController extends BaseController
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
                'only' => ['index'],
                'rules' => [
                    ['allow' => true, 'actions' => ['index'], 'roles' => ['setContentTypes']],
                ],
            ],
        ];
    }

    /**
     * Lists all ContentType models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ContentType::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Enables or disables a Device.
     *
     * @param int $id screen id
     *
     * @return \yii\web\Response
     */
    public function actionToggle($id)
    {
        $model = $this->findModel($id);

        $model->enabled = !$model->enabled;
        $model->save();

        return $this->smartGoBack();
    }

    /**
     * Finds the ContentType model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     *
     * @return ContentType the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ContentType::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested content does not exist.'));
        }
    }
}
