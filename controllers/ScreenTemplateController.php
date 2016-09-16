<?php

namespace app\controllers;

use Yii;
use app\models\ContentType;
use app\models\ScreenTemplate;
use app\models\upload\BackgroundUpload;
use app\models\Field;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;

/**
 * ScreentemplateController implements the CRUD actions for ScreenTemplate model.
 */
class ScreenTemplateController extends Controller
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
     * Lists all ScreenTemplate models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ScreenTemplate::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ScreenTemplate model.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        $screenTemplate = $this->findModel($id);

        return $this->render('view', [
            'model' => $screenTemplate,
            'background' => Url::to($screenTemplate->background),
            'fields' => $screenTemplate->fieldsArray,
            'setFieldPosUrl' => Url::to([Yii::$app->controller->id.'/set-field-pos', 'id' => '']),
            'editFieldUrl' => Url::to([Yii::$app->controller->id.'/edit-field', 'id' => '']),
        ]);
    }

    public function actionEditField($id)
    {
        $field = Field::find()->where(['id' => $id])->with('contentTypes')->one();
        if ($field === null) {
            return;
        }

        if ($field->load(Yii::$app->request->post())) {
            if ($field->save()) {
                return '';
            }
        }

        $contentTypes = ContentType::find()->all();

        return $this->renderAjax('editfield', [
            'field' => $field,
            'contentTypes' => array_reduce($contentTypes, function ($a, $c) {
                if (!$c->self_update) {
                    $a[$c->id] = $c->name;
                }

                return $a;
            }, []),
            'selfContentTypes' => array_reduce($contentTypes, function ($a, $c) {
                if ($c->self_update) {
                    $a[$c->id] = $c->name;
                }

                return $a;
            }, []),
        ]);
    }

    public function actionSetFieldPos($id = null)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if ($id !== null) {
            $field = Field::find()->where(['id' => $id])->one();
            if ($field === null) {
                return ['success' => false, 'message' => 'No such field'];
            }
        } else {
            $field = new Field();
        }

        if ($field->load(Yii::$app->request->post())) {
            if ($field->save()) {
                return ['success' => true, 'id' => $field->id];
            }
        }

        return ['success' => false, 'message' => $field->errors];
    }

    public function actionDeleteField($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $field = Field::find()->where(['id' => $id])->with('contentTypes')->one();
        if ($field === null || $field->delete() === false) {
            return ['success' => false, 'message' => Yii::t('app', 'Deletion failed')];
        }

        return ['success' => true];
    }

    /**
     * Creates a new ScreenTemplate model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ScreenTemplate();
        $image = new BackgroundUpload();

        if ($model->load(Yii::$app->request->post())) {
            $image->content = UploadedFile::getInstance($image, 'content');
            $imagePath = $image->upload();
            if ($imagePath) {
                $model->background = Url::to($imagePath);
            }

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $backgrounds = self::getBackgroundRadios();

        return $this->render('create', [
            'model' => $model,
            'image' => $image,
            'backgrounds' => $backgrounds,
        ]);
    }

    /**
     * Updates an existing ScreenTemplate model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $image = new BackgroundUpload();

        if ($model->load(Yii::$app->request->post())) {
            $image->content = UploadedFile::getInstance($image, 'content');
            $imagePath = $image->upload();
            if ($imagePath) {
                $model->background = Url::to($imagePath);
            }

            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        $backgrounds = self::getBackgroundRadios();

        return $this->render('update', [
            'model' => $model,
            'image' => $image,
            'backgrounds' => $backgrounds,
        ]);
    }

    public static function getBackgroundRadios()
    {
        $backgrounds = BackgroundUpload::getAllWithPath();

        $radio = [];
        foreach ($backgrounds as $name => $path) {
            $radio[$path] = '<img src="'.Url::to($path).'" alt="'.$name.'" class="img-preview"/><br />'.$name;
        }

        return $radio;
    }

    /**
     * Deletes an existing ScreenTemplate model.
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
     * Finds the ScreenTemplate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     *
     * @return ScreenTemplate the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ScreenTemplate::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
