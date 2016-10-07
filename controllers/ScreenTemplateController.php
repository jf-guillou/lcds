<?php

namespace app\controllers;

use Yii;
use app\models\ContentType;
use app\models\ScreenTemplate;
use app\models\types\Background;
use app\models\Field;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * ScreentemplateController implements the CRUD actions for ScreenTemplate model.
 */
class ScreenTemplateController extends BaseController
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
                'only' => ['index', 'view', 'add-field', 'get-field', 'edit-field', 'set-field-pos', 'delete-field', 'create', 'update', 'delete'],
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'view', 'add-field', 'get-field', 'edit-field', 'set-field-pos', 'delete-field', 'create', 'update', 'delete'], 'roles' => ['setTemplates']],
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

    public function actionAddField($templateId)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $field = new Field();
        $field->template_id = $templateId;
        $max = mt_getrandmax();
        $field->x1 = self::randf(0.1, 0.4);
        $field->y1 = self::randf(0.1, 0.4);
        $field->x2 = self::randf($field->x1, 0.8);
        $field->y2 = self::randf($field->y1, 0.8);

        if ($field->save()) {
            return ['success' => true, 'field' => $field];
        } else {
            return ['success' => false, 'message' => Yii::t('app', 'Failed to insert new field')];
        }
    }

    public static function randf($min = 0.0, $max = 1.0)
    {
        return mt_rand($min * mt_getrandmax(), $max * mt_getrandmax()) / mt_getrandmax();
    }

    public function actionGetField($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $field = Field::find()->where(['id' => $id])->with('contentTypes')->one();

        if ($field === null) {
            return ['success' => false, 'message' => Yii::t('app', 'Field not found')];
        } else {
            return ['success' => true, 'field' => $field, 'contentTypes' => $field->contentTypes];
        }
    }

    public function actionEditField($id)
    {
        $field = Field::find()->where(['id' => $id])->with('contentTypes')->one();
        if ($field === null) {
            return;
        }

        if ($field->load(Yii::$app->request->post())) {
            $newTypeIds = Yii::$app->request->post($field->formName())['contentTypes'];
            if (!is_array($newTypeIds)) {
                $newTypeIds = [];
            }
            $oldTypeIds = array_map(function ($c) {
                return $c->id;
            }, $field->contentTypes);

            $unlink = array_diff($oldTypeIds, $newTypeIds);
            $unlinkModels = ContentType::find()->where(['id' => $unlink])->all();
            foreach ($unlinkModels as $u) {
                $field->unlink('contentTypes', $u, true);
            }
            $link = array_diff($newTypeIds, $oldTypeIds);
            $linkModels = ContentType::find()->where(['id' => $link])->all();
            foreach ($linkModels as $l) {
                $field->link('contentTypes', $l);
            }

            if ($field->save()) {
                return '';
            }
        }

        $allContentTypes = ContentType::find()->all();
        $contentTypesArray = array_reduce($allContentTypes, function ($a, $c) {
            $a[$c->id] = $c->name;

            return $a;
        }, []);
        $selfCTypes = array_reduce($allContentTypes, function ($a, $c) {
            if ($c->selfUpdate) {
                $a[] = $c->id;
            }

            return $a;
        }, []);

        return $this->renderAjax('editfield', [
            'field' => $field,
            'contentTypesArray' => $contentTypesArray,
            'selfContentIds' => $selfCTypes,
        ]);
    }

    public function actionSetFieldPos($id = null)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if ($id !== null) {
            $field = Field::find()->where(['id' => $id])->one();
            if ($field === null) {
                return ['success' => false, 'message' => Yii::t('app', 'No such field')];
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
        $image = new Background();

        if ($model->load(Yii::$app->request->post())) {
            if ($image->upload(UploadedFile::getInstance($image, 'data'))) {
                $model->background = Url::to($image->getWebFilepath());
            }

            if ($model->save()) {
                $image->backgroundSet();

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
        $image = new Background();

        if ($model->load(Yii::$app->request->post())) {
            if ($image->upload(UploadedFile::getInstance($image, 'data'))) {
                $model->background = Url::to($image->getWebFilepath());
            }

            if ($model->save()) {
                $image->backgroundSet();

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
        $backgrounds = Background::getAllWithPath();

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
