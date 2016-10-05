<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();

        /*
         * Rules
         */

        /*
         * Permissions
         */

        $administration = $auth->createPermission('admin');
        $administration->description = 'Administrate everyting';
        $auth->add($administration);

        $setScreens = $auth->createPermission('setScreens');
        $setScreens->description = 'Manage screens';
        $auth->add($setScreens);

        $setTemplates = $auth->createPermission('setTemplates');
        $setTemplates->description = 'Manage screen templates';
        $auth->add($setTemplates);

        $setFlowContent = $auth->createPermission('setFlowContent');
        $setFlowContent->description = 'Manage flow content';
        $auth->add($setFlowContent);

        $upload = $auth->createPermission('upload');
        $upload->description = 'Upload content';
        $auth->add($upload);

        $setOwnFlowContent = $auth->createPermission('setOwnFlowContent');
        $setOwnFlowContent->description = 'Manage owned flow content';
        $auth->add($setOwnFlowContent);

        /*
         * Roles
         */

        $contentCreator = $auth->createRole('Content creator');
        $auth->add($contentCreator);
        $auth->addChild($contentCreator, $upload);
        $auth->addChild($contentCreator, $setOwnFlowContent);

        $screenManager = $auth->createRole('Screen manager');
        $auth->add($screenManager);
        $auth->addChild($screenManager, $setFlowContent);
        $auth->addChild($screenManager, $setTemplates);
        $auth->addChild($screenManager, $setScreens);
        $auth->addChild($screenManager, $contentCreator);

        $admin = $auth->createRole('Administrator');
        $auth->add($admin);
        $auth->addChild($admin, $administration);
        $auth->addChild($admin, $screenManager);
    }
}
