<?php

namespace app\controllers;

use Yii;
use app\models\Content;
use app\models\ContentType;
use app\models\Flow;
use yii\helpers\Url;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ContentController implements the CRUD actions for Content model.
 */
class ContentController extends Controller
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
     * Lists all Content models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Content::find()->joinWith('type'),
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
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Content model.
     * If creation is successful, the browser will be redirected to the 'view' page.
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
                'contentTypes' => ContentType::getAllList(false),
            ]);
        }
    }

    public function actionGenerate($flowId, $type = null)
    {
        $flow = Flow::findOne($flowId);
        if ($flow === null) {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }

        $contentType = ContentType::findOne($type);
        if ($contentType === null) {
            $dataProvider = new ActiveDataProvider([
                'query' => ContentType::getQuery(false),
            ]);

            return $this->render('type-choice', [
                'dataProvider' => $dataProvider,
                'flow' => $flowId,
            ]);
        } else {
            $model = new Content();
            if ($model->load(Yii::$app->request->post())) {
                $model->flow_id = $flow->id;
                $model->type_id = $contentType->id;
                if ($model->save()) {
                    return $this->redirect(['flow/view', 'id' => $flow->id]);
                }
            }
            switch ($contentType->kind) {
                case ContentType::KINDS['FILE']:
                    // FILE implies content upload (images/videos)
                case ContentType::KINDS['URL']:
                    // URL allows content hotlinks, like images
                    // There's not much to process, simply input url in data
                case ContentType::KINDS['TEXT']:
                    // Same as URL, text doesn't require processing
                    $model->loadDefaultValues();

                    return $this->render('type/'.$contentType->kind, [
                            'type' => $contentType,
                            'model' => $model,
                            'uploadUrl' => Url::to(['content/upload', 'type' => $type]),
                            'sideloadUrl' => Url::to(['content/sideload', 'type' => $type]),
                        ]);
                    break;

                case ContentType::KINDS['RAW']:
                    // RAW ContentType doesn't support Content
                    // Everything should be handled by ContentType alone
                default:
                    throw new NotFoundHttpException(Yii::t('app', 'The requested content type is not supported.'));
            }
        }

        return $this->redirect(['flows/view', 'id' => $flowId]);
    }

    public function actionUpload($type)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $contentType = ContentType::findOne($type);
        if ($contentType === null || $contentType->class_name === null) {
            return ['success' => false, 'message' => Yii::t('app', 'Unsupported content type')];
        }

        $className = 'app\\models\\upload\\'.$contentType->class_name;
        $upload = new $className();
        $upload->content = UploadedFile::getInstanceByName('content');
        if ($upload->upload()) {
            if ($upload->isValid()) {
                return ['success' => true, 'path' => $upload->path, 'duration' => $upload->duration];
            } else {
                $upload->delete();

                return ['success' => false, 'message' => Yii::t('app', 'Incorrect file')];
            }
        } else {
            if (array_key_exists('content', $upload->errors)) {
                return ['success' => false, 'message' => implode(', ', $upload->errors['content'])];
            } elseif ($upload->content->error) {
                return ['success' => false, 'message' => Yii::t('app', 'Upload failed {code}', ['code' => $upload->content->error])];
            }
        }

        return ['success' => false, 'message' => Yii::t('app', 'Upload failed')];
    }

    public function actionSideload($type, $url)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $contentType = ContentType::findOne($type);
        if ($contentType === null || $contentType->class_name === null) {
            return ['success' => false, 'message' => Yii::t('app', 'Unsupported content type')];
        }

        $className = 'app\\models\\upload\\'.$contentType->class_name;
        $upload = new $className();
        if ($upload->sideload($url)) {
            if ($upload->isValid()) {
                return ['success' => true, 'path' => $upload->path, 'duration' => $upload->duration];
            } else {
                $upload->delete();

                return ['success' => false, 'message' => Yii::t('app', 'Incorrect file')];
            }
        } else {
            if ($upload->error) {
                return ['success' => false, 'message' => $upload->error];
            }
        }

        return ['success' => false, 'message' => Yii::t('app', 'Sideload failed')];
    }

    public function actionPollProgress()
    {
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
                'contentTypes' => ContentType::getAllList(false),
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

        if ($model->shouldDeleteFile()) {
            unlink($model->getRealFilepath());
        }

        $model->delete();

        return $this->redirect(['index']);
    }

    public function actionToggle($id)
    {
        $model = $this->findModel($id);

        $model->enabled = !$model->enabled;

        $model->save();

        return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : null));
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

    public function actionTest($url)
    {
        var_dump(\app\helpers\VideoDL::dl($url));
    }
}
