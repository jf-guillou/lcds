<?php

namespace app\controllers;

use Yii;

/**
 * SetupController allows to setup the site and database.
 */
class SetupController extends BaseController
{
    /**
     * Run migrations.
     */
    public function actionIndex()
    {
        Yii::$app->runAction('migrate', ['interactive' => false]);

        return $this->goHome();
    }
}
