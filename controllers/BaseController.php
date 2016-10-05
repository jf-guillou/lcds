<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

class BaseController extends Controller
{
    public function init()
    {
        parent::init();

        $language = Yii::$app->session->get('language');
        if (!$language) { // Not in session
            $language = Yii::$app->request->cookies->getValue('language');
            if (!$language) { // Not in cookie
                if (!Yii::$app->user->isGuest) { // Logged in
                    $language = Yii::$app->user->identity->getLanguage();
                    if (!$language) { // Not in DB
                        $language = \Yii::$app->sourceLanguage;
                        Yii::$app->user->identity->setLanguage($language);
                    }
                } else { // Not logged in
                    $language = Yii::$app->sourceLanguage;
                }

                Yii::$app->response->cookies->add(new \yii\web\Cookie([
                    'name' => 'language',
                    'value' => $language,
                ]));
            }
            Yii::$app->session->set('language', $language);
        }

        Yii::$app->language = $language;
    }
}
