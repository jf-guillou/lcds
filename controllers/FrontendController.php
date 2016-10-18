<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\db\Expression;
use app\models\Screen;
use app\models\Field;
use app\models\Flow;
use app\models\Content;
use app\models\ContentType;

/**
 * FrontendController implements the actions used by screens.
 */
class FrontendController extends BaseController
{
    public $layout = 'frontend';
    public $defaultScreen = 1;

    /**
     * Index redirects to default screen.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->redirect(['screen', 'id' => $this->defaultScreen]);
    }

    /**
     * Initializes screen content html structure.
     *
     * @param int $id screen id
     *
     * @return mixed
     */
    public function actionScreen($id)
    {
        $screen = Screen::find()->where([Screen::tableName().'.id' => $id])->joinWith(['template', 'template.fields', 'template.fields.contentTypes'])->one();
        if ($screen === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $content = [
            'name' => $screen->name,
            'screenCss' => $screen->template->css,
            'background' => $screen->template->background->uri,
            'fields' => $screen->template->fields,
            'updateUrl' => Url::to(['frontend/update', 'id' => $id]),
            'nextUrl' => Url::to(['frontend/next', 'id' => $id, 'fieldid' => '']),
            'types' => ContentType::getAll(),
        ];

        return $this->render('default', $content);
    }

    /**
     * Sends last screen update timestamp, indicating if refresh is needed.
     *
     * @api
     *
     * @param int $id screen id
     *
     * @return string json last update
     */
    public function actionUpdate($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $screen = Screen::find()->where([Screen::tableName().'.id' => $id])->one();
        if ($screen === null) {
            return ['success' => false, 'message' => 'Unknown screen'];
        }

        return ['success' => true, 'data' => $screen->last_changes];
    }

    /**
     * Sends all available content for a specific field.
     *
     * @param int $id      screen id
     * @param int $fieldid field id
     *
     * @return string json array
     */
    public function actionNext($id, $fieldid)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // Get screen
        $screen = Screen::find()->where([Screen::tableName().'.id' => $id])->joinWith(['flows'])->one();
        if ($screen === null) {
            return ['success' => false, 'message' => 'Unknown screen'];
        }

        // Get field
        $field = Field::find()->where(['id' => $fieldid])->one();
        if ($field === null) {
            return ['success' => false, 'message' => 'Unknown field'];
        }

        // Get all flows for screen
        $flows = $screen->allFlows();

        // Get all flow ids
        $flowIds = array_map(function ($e) {
            return $e->id;
        }, $flows);

        // Get all content type ids
        $contentTypes = array_map(function ($e) {
            return $e->id;
        }, $field->contentTypes);

        // Get content for flows and field type
        $contents = Content::find()
            ->joinWith(['flow'])
            ->where(['type_id' => $contentTypes])
            ->andWhere([Flow::tableName().'.id' => $flowIds])
            ->andWhere(['enabled' => true])
            ->andWhere(['or', ['start_ts' => null], ['<', 'start_ts', new Expression('NOW()')]])
            ->andWhere(['or', ['end_ts' => null], ['>', 'end_ts', new Expression('NOW()')]])
            ->all();

        $next = array_map(function ($c) use ($field) {
            return [
                'id' => $c->id,
                'data' => $field->mergeData($c->getData()),
                'duration' => $c->duration,
                'type' => $c->type_id,
            ];
        }, $contents);

        return ['success' => true, 'next' => $next];
    }

    /**
     * Dynamic content fetcher based on content type and field data.
     *
     * @param string $typeId content type
     * @param string $data   content data
     *
     * @return mixed content
     */
    public function actionGet($typeId, $data)
    {
        $contentType = ContentType::findOne($typeId);
        if ($contentType !== null) {
            $model = Content::newFromType($contentType->id);

            return $model->get($data);
        }
    }
}
