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
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index', 'view', 'generate', 'upload', 'sideload', 'update', 'delete', 'toggle'],
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'view', 'generate', 'upload', 'sideload', 'update', 'delete', 'toggle'], 'roles' => ['@']],
                ],
            ],
        ];
    }

    /**
     * Lists all Content models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $query = Content::availableQuery(Yii::$app->user);
        if ($query === null) {
            throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'You do not have enough rights to view this content.'));
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->attributes['type.name'] = [
            'asc' => [ContentType::tableName() . '.id' => SORT_ASC],
            'desc' => [ContentType::tableName() . '.id' => SORT_DESC],
        ];

        $dataProvider->sort->attributes['flow.name'] = [
            'asc' => [Flow::tableName() . '.id' => SORT_ASC],
            'desc' => [Flow::tableName() . '.id' => SORT_DESC],
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
     * @return string
     */
    public function actionView($id)
    {
        $model = $this->findViewableModel($id, Yii::$app->user);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Content model with type choice assistance.
     *
     * @param int    $flowId
     * @param string $type   content type
     *
     * @return \yii\web\Response|string redirect or render
     */
    public function actionGenerate($flowId, $type = null)
    {
        $flow = Flow::findOne($flowId);
        if ($flow === null) {
            throw new NotFoundHttpException(Yii::t('app', 'The requested flow does not exist.'));
        }

        if (!$flow->canView(Yii::$app->user)) {
            throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'You do not have enough rights to view this content.'));
        }

        $contentType = ContentType::findOne($type);
        if ($contentType === null) {
            $types = ContentType::getAll(false);

            return $this->render('type-choice', [
                'types' => $types,
                'flow' => $flowId,
            ]);
        } else {
            $model = new Content(['flow_id' => $flow->id, 'type_id' => $contentType->id]);
            if ($model->load(Yii::$app->request->post())) {
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
                    return $this->render('type/' . $contentType->input, [
                        'type' => $contentType,
                        'model' => $model,
                        'uploadUrl' => Url::to(['content/upload', 'type' => $type]),
                        'sideloadUrl' => Url::to(['content/sideload', 'type' => $type]),
                    ]);
                case ContentType::KINDS['NONE']:
                case ContentType::KINDS['RAW']:
                    // RAW ContentType doesn't support Content
                    // Everything should be handled by ContentType alone
                default:
                    throw new NotFoundHttpException(Yii::t('app', 'The requested content type is not supported.'));
            }
        }
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

        $model = new Content(['type_id' => $type]);
        if (($res = $model->type->upload(UploadedFile::getInstanceByName('content'))) !== false) {
            return ['success' => true, 'filepath' => $res['tmppath'], 'duration' => $res['duration'], 'filename' => $res['filename']];
        }

        return ['success' => false, 'message' => $model->type->getLoadError()];
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

        $model = new Content(['type_id' => $type]);
        if (($res = $model->type->sideload($url)) !== false) {
            return ['success' => true, 'filepath' => $res['tmppath'], 'duration' => $res['duration'], 'filename' => $res['filename']];
        }

        return ['success' => false, 'message' => $model->type->getLoadError()];
    }

    /**
     * Updates an existing Content model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id
     *
     * @return \yii\web\Response|string redirect or render
     */
    public function actionUpdate($id)
    {
        $model = $this->findViewableModel($id, Yii::$app->user);

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
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        $model = $this->findViewableModel($id, Yii::$app->user);

        $model->delete();

        return $this->smartGoBack();
    }

    /**
     * Renders specific content for preview.
     *
     * @param int $id content id
     *
     * @return string HTML render
     */
    public function actionPreview($id)
    {
        $model = $this->findViewableModel($id, Yii::$app->user);

        return $this->renderPartial('preview', [
            'type' => $model->type,
            'data' => $model->getData(),
        ]);
    }

    /**
     * Enables or disable a specific content.
     *
     * @param int $id content id
     *
     * @return \yii\web\Response
     */
    public function actionToggle($id)
    {
        $model = $this->findViewableModel($id, Yii::$app->user);

        $model->enabled = !$model->enabled;

        $model->save();

        return $this->smartGoBack();
    }

    /**
     * Finds the Content model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * If the user has not enough rights, a 403 HTTP exception will be thrown.
     *
     * @param int           $id
     * @param \yii\web\User $user
     *
     * @return Content the loaded model
     *
     * @throws NotFoundHttpException  if the model cannot be found
     * @throws ForbiddenHttpException if the model cannot be accessed
     */
    protected function findViewableModel($id, $user)
    {
        $model = $this->findModel($id);
        if ($model->canView($user)) {
            return $model;
        }

        throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'You do not have enough rights to view this content.'));
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
            throw new NotFoundHttpException(Yii::t('app', 'The requested content does not exist.'));
        }
    }
}
