<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\NotFoundHttpException;
use yii\db\Expression;
use app\models\Screen;
use app\models\Field;
use app\models\Flow;
use app\models\Content;
use app\models\ContentType;

class FrontendController extends BaseController
{
    public $layout = 'frontend';
    public $defaultScreen = 1;

    public function actionIndex()
    {
        return $this->redirect(['screen', 'id' => $this->defaultScreen]);
    }

    public function actionScreen($id)
    {
        $screen = Screen::find()->where([Screen::tableName().'.id' => $id])->joinWith(['template', 'template.fields', 'template.fields.contentTypes'])->one();
        if ($screen === null) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $content = [
            'name' => $screen->name,
            'screenCss' => $screen->template->css,
            'background' => Url::to($screen->template->background),
            'fields' => $screen->template->fields,
            'updateUrl' => Url::to(['frontend/update', 'id' => $id]),
            'nextUrl' => Url::to(['frontend/next', 'id' => $id, 'fieldid' => '']),
            'types' => ContentType::find()->all(),
        ];

        return $this->render('default', $content);
    }

    public function actionUpdate($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $screen = Screen::find()->where([Screen::tableName().'.id' => $id])->one();
        if ($screen === null) {
            return ['success' => false, 'message' => 'Unknown screen'];
        }

        return ['success' => true, 'data' => $screen->last_changes];
    }

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
            ->joinWith(['flow', 'type'])
            ->where(['type_id' => $contentTypes])
            ->andWhere([Flow::tableName().'.id' => $flowIds])
            ->andWhere(['enabled' => true])
            ->andWhere(['or', ['start_ts' => null], ['<', 'start_ts', new Expression('NOW()')]])
            ->andWhere(['or', ['end_ts' => null], ['>', 'end_ts', new Expression('NOW()')]])
            ->all();

        $next = array_map(function ($c) use ($field) {
            $data = $c->data;
            if ($c->type->append_params) {
                $data .= (strpos($data, '?') === false ? '?' : '&').str_replace(['%x1%', '%x2%', '%y1%', '%y2%'], [$field->x1, $field->x2, $field->y1, $field->y2], $c->type->append_params);
            }
            if ($field->append_params) {
                $data .= (strpos($data, '?') === false ? '?' : '&').str_replace(['%x1%', '%x2%', '%y1%', '%y2%'], [$field->x1, $field->x2, $field->y1, $field->y2], $field->append_params);
            }

            switch ($c->type->kind) {
                case ContentType::KINDS['FILE']:
                    $data = Url::to($data);
                    break;
                case ContentType::KINDS['TEXT']:
                    $data = nl2br(Html::encode($data));
                    break;
            }

            return [
                'id' => $c->id,
                'data' => str_replace('%data%', $data, $c->type->html),
                'duration' => $c->duration,
                'type' => $c->type_id,
            ];
        }, $contents);

        return ['success' => true, 'next' => $next];
    }
}
