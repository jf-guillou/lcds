<?php

namespace app\controllers;

use Yii;
use yii\web\Request;
use yii\web\Controller;

/**
 * BaseController allows to set global controller actions.
 */
class BaseController extends Controller
{
    /**
     * Tries to redirect to appropriate page
     * On delete action, go to item list
     * Else go back to referer.
     *
     * @return \yii\web\Response redirect
     */
    public function smartGoBack()
    {
        // Current action is delete
        if (preg_match('@(.*)/delete$@', Yii::$app->request->pathInfo, $res)) {
            $controller = $res[1];
            // Referer is view of deleted item
            if (preg_match('@'.$controller.'/view@', Yii::$app->request->referrer)) {
                return $this->redirect([$controller.'/index']);
            }
        }

        return $this->redirect(Yii::$app->request->referrer);
    }
}
