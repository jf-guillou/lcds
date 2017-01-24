<?php

namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\db\Expression;
use app\helpers\Alert;
use app\models\Screen;
use app\models\Device;
use app\models\Field;
use app\models\Flow;
use app\models\Content;
use app\models\ContentType;

/**
 * FrontendController implements the actions used by screens.
 */
class FrontendController extends BaseController
{
    const ID = 'device_id';
    const EXP_YEARS = 10;
    public $layout = 'frontend';

    /**
     * Index redirects to associated screen ID.
     * Checks authorization based on session & cookie
     * Else create a new screen and display auth.
     *
     * @return string
     */
    public function actionIndex()
    {
        $device = $this->getClientDevice();

        if ($device !== null) { // Associated device
            // Check session
            if (!$this->isClientAuth()) {
                $this->setClientAuth($device);
            }

            if (!$device->enabled) {
                // Render enable view
                return $this->render('err/authorize', [
                    'url' => Url::to(['device/view', 'id' => $device->id], true),
                ]);
            }

            $screen = $device->getNextScreen();
            if (!$screen) {
                // Render add screen view
                return $this->render('err/missing-screen', [
                    'url' => Url::to(['device/view', 'id' => $device->id], true),
                ]);
            }

            return $this->redirect(['screen', 'id' => $screen->id]);
        }

        // New device
        $cookies = Yii::$app->response->cookies;

        $device = new Device();
        $device->name = Yii::$app->request->getUserIP();
        $device->description = Yii::t('app', 'New unauthorized device');
        $device->save();
        $device->id = $device->lastId;

        $cookies->add(new \yii\web\Cookie([
            'name' => self::ID,
            'value' => $device->id,
            'expire' => time() + (self::EXP_YEARS * 365 * 24 * 60 * 60),
        ]));

        // Render enable view
        return $this->render('err/authorize', [
            'url' => Url::to(['device/view', 'id' => $device->id], true),
        ]);
    }

    /**
     * Initializes screen content html structure.
     *
     * @param int $id screen id
     *
     * @return \yii\web\Response|string redirect or render
     */
    public function actionScreen($id, $preview = false)
    {
        // Session auth
        if (!$this->isClientAuth() && !Yii::$app->user->can('previewScreen')) {
            return $this->redirect(['index']);
        }

        $screen = Screen::find()->where([Screen::tableName().'.id' => $id])->joinWith(['template', 'template.fields', 'template.fields.contentTypes'])->one();
        if ($screen === null) {
            throw new NotFoundHttpException(Yii::t('app', 'The requested screen does not exist.'));
        }
        $content = [
            'name' => $screen->name,
            'screenCss' => $screen->template->css,
            'background' => $screen->template->background->uri,
            'fields' => $screen->template->fields,
            'updateUrl' => $preview ? null : Url::to(['frontend/update', 'id' => $id]),
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
        // Session auth
        if (!$this->isClientAuth()) { // Disable update if no device association
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        $screen = Screen::find()->where([Screen::tableName().'.id' => $id])->one();
        if ($screen === null) {
            return ['success' => false, 'message' => 'Unknown screen'];
        }

        $device = $this->getClientDevice();
        $nextScreen = $device ? $device->getNextScreen($screen->id) : null;

        return ['success' => true, 'data' => [
            'lastChanges' => $screen->last_changes,
            'duration' => $nextScreen ? $screen->duration : 0,
            'nextScreenUrl' => $nextScreen ? Url::to(['frontend/screen', 'id' => $nextScreen->id]) : null,
        ]];
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
        // Session auth
        if (!$this->isClientAuth() && !Yii::$app->user->can('previewScreen')) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }

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
            ->orderBy('duration ASC')
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
     * Send an screen reload order to device.
     *
     * @param int $id screen id
     *
     * @return \yii\web\Response
     */
    public function actionForceReload($id)
    {
        if (Yii::$app->user->can('setScreens')) {
            $screen = Screen::findOne($id);
            if ($screen !== null) {
                Alert::add('Screen will reload', Alert::SUCCESS);
                $screen->setModified();

                return $this->smartGoBack();
            }
        }

        Alert::add('Failed to force Screen reload', Alert::DANGER);

        return $this->smartGoBack();
    }

    /**
     * Checks client session for device ID.
     *
     * @return bool is authenticated
     */
    private function isClientAuth()
    {
        return Yii::$app->session->get(self::ID) !== null;
    }

    /**
     * Set session with device ID, also add to DB last auth timestamp.
     *
     * @param \app\models\Device $device
     */
    private function setClientAuth($device)
    {
        Yii::$app->session->set(self::ID, $device->id);
        $device->setAuthenticated();
    }

    /**
     * Look for client device ID from session & cookie.
     *
     * @return int|null device ID
     */
    private function getClientId()
    {
        $id = Yii::$app->session->get(self::ID);
        if ($id !== null) {
            return $id;
        }

        $cookies = Yii::$app->request->cookies;
        $id = $cookies->getValue(self::ID);
        if ($id !== null) {
            return $id;
        }

        return;
    }

    /**
     * Get client device based on session & cookie.
     *
     * @return \app\models\Device|null device
     */
    private function getClientDevice()
    {
        $id = $this->getClientId();
        if ($id === null) {
            return;
        }

        return Device::findOne($id);
    }
}
