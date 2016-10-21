<?php

namespace app\controllers;

use Yii;
use app\models\Content;
use app\models\ContentType;
use app\models\Flow;
use yii\helpers\Url;
use yii\data\ActiveDataProvider;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * ContentController implements the CRUD actions for Content model.
 */
class ContentController extends BaseController
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
                'only' => ['index', 'view', 'create', 'generate', 'upload', 'sideload', 'update', 'delete', 'toggle'],
                'rules' => [
                    ['allow' => true, 'actions' => ['create'], 'roles' => ['setContent']],
                    ['allow' => true, 'actions' => ['index', 'view', 'generate', 'upload', 'sideload', 'update', 'delete', 'toggle'], 'roles' => ['@']],
                ],
            ],
        ];
    }

    /**
     * Lists all Content models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $query = Content::availableQuery(Yii::$app->user);
        if ($query === null) {
            throw new \yii\web\ForbiddenHttpException();
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->attributes['type.name'] = [
            'asc' => [ContentType::tableName().'.name' => SORT_ASC],
            'desc' => [ContentType::tableName().'.name' => SORT_DESC],
        ];

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Content model.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if (!$model->canView(Yii::$app->user)) {
            throw new \yii\web\ForbiddenHttpException();
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Content model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $flowId
     *
     * @return mixed
     */
    public function actionCreate($flowId)
    {
        $model = new Content();
        $flow = Flow::findOne($flowId);
        if ($flow === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $model->flow_id = $flow->id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            $model->loadDefaultValues();

            return $this->render('create', [
                'model' => $model,
                'contentTypes' => ContentType::getAllList(false, true),
            ]);
        }
    }

    /**
     * Creates a new Content model with type choice assistance.
     *
     * @param int    $flowId
     * @param string $type   content type
     *
     * @return mixed
     */
    public function actionGenerate($flowId, $type = null)
    {
        $flow = Flow::findOne($flowId);
        if ($flow === null) {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }

        if (!$flow->canView(Yii::$app->user)) {
            throw new \yii\web\ForbiddenHttpException();
        }

        $contentType = ContentType::findOne($type);
        if ($contentType === null) {
            $types = ContentType::getAll(false, true);

            return $this->render('type-choice', [
                'types' => $types,
                'flow' => $flowId,
            ]);
        } else {
            $model = Content::newFromType($contentType->id);
            if ($model->load(Yii::$app->request->post())) {
                $model->flow_id = $flow->id;
                $model->type_id = $contentType->id;
                if ($model->save()) {
                    return $this->redirect(['flow/view', 'id' => $flow->id]);
                }
            } else {
                $model->loadDefaultValues();
            }

            switch ($contentType->input) {
                case ContentType::KINDS['FILE']:
                    // FILE implies content upload (images/videos)
                case ContentType::KINDS['URL']:
                    // URL allows content hotlinks, like images
                    // There's not much to process, simply input url in data
                case ContentType::KINDS['POS']:
                    // Latitude & longitude
                case ContentType::KINDS['TEXT']:
                    // Same as URL, text doesn't require processing
                    return $this->render('type/'.$contentType->input, [
                            'type' => $contentType,
                            'model' => $model,
                            'uploadUrl' => Url::to(['content/upload', 'type' => $type]),
                            'sideloadUrl' => Url::to(['content/sideload', 'type' => $type]),
                        ]);
                    break;

                case ContentType::KINDS['NONE']:
                case ContentType::KINDS['RAW']:
                    // RAW ContentType doesn't support Content
                    // Everything should be handled by ContentType alone
                default:
                    throw new NotFoundHttpException(Yii::t('app', 'The requested content type is not supported.'));
            }
        }

        return $this->redirect(['flows/view', 'id' => $flowId]);
    }

    /**
     * Receives an uploaded file and responds with filepath.
     *
     * @api
     *
     * @param string $type content type
     *
     * @return string json status
     */
    public function actionUpload($type)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!Yii::$app->user->can('upload')) {
            return ['success' => false, 'message' => Yii::t('app', 'Not authorized')];
        }

        $upload = Content::newFromType($type);
        if (($res = $upload->upload(UploadedFile::getInstanceByName('content'))) !== false) {
            return ['success' => true, 'filepath' => $res['tmppath'], 'duration' => $res['duration'], 'filename' => $res['filename']];
        }

        return ['success' => false, 'message' => $upload->getLoadError()];
    }

    /**
     * Receives an url to download on server -- sideloading.
     *
     * @api
     *
     * @param string $type content type
     * @param string $url
     *
     * @return string json status
     */
    public function actionSideload($type, $url)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (!Yii::$app->user->can('upload')) {
            return ['success' => false, 'message' => Yii::t('app', 'Not authorized')];
        }

        $upload = Content::newFromType($type);
        if (($res = $upload->sideload($url)) !== false) {
            return ['success' => true, 'filepath' => $res['tmppath'], 'duration' => $res['duration'], 'filename' => $res['filename']];
        }

        return ['success' => false, 'message' => $upload->getLoadError()];
    }

    /**
     * Updates an existing Content model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (!$model->canView(Yii::$app->user)) {
            throw new \yii\web\ForbiddenHttpException();
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
                'contentTypes' => ContentType::getAllList(false, true),
            ]);
        }
    }

    /**
     * Deletes an existing Content model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if (!$model->canView(Yii::$app->user)) {
            throw new \yii\web\ForbiddenHttpException();
        }

        $model->delete();

        return $this->smartGoBack();
    }

    /**
     * Enables or disable a specific content.
     *
     * @param int $id content id
     *
     * @return mixed
     */
    public function actionToggle($id)
    {
        $model = $this->findModel($id);

        if (!$model->canView(Yii::$app->user)) {
            throw new \yii\web\ForbiddenHttpException();
        }

        $model->enabled = !$model->enabled;

        $model->save();

        return $this->smartGoBack();
    }

    /**
     * Finds the Content model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     *
     * @return Content the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Content::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
