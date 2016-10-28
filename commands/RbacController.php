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

        // Admin
        $administration = $auth->createPermission('admin');
        $administration->description = 'Administrate everyting';
        $auth->add($administration);

        // Devices
        $setDevices = $auth->createPermission('setDevices');
        $setDevices->description = 'Manage devices';
        $auth->add($setDevices);

        // Screens
        $setScreens = $auth->createPermission('setScreens');
        $setScreens->description = 'Manage screens';
        $auth->add($setScreens);

        $previewScreen = $auth->createPermission('previewScreen');
        $previewScreen->description = 'Preview screens';
        $auth->add($previewScreen);

        // Templates
        $setTemplates = $auth->createPermission('setTemplates');
        $setTemplates->description = 'Manage screen templates';
        $auth->add($setTemplates);

        // Flows
        $setFlows = $auth->createPermission('setFlows');
        $setFlows->description = 'Manage flows';
        $auth->add($setFlows);

        // Flow content
        $setFlowContent = $auth->createPermission('setFlowContent');
        $setFlowContent->description = 'Manage flow content';
        $auth->add($setFlowContent);

        // Own flow content
        $setOwnFlowContent = $auth->createPermission('setOwnFlowContent');
        $setOwnFlowContent->description = 'Manage owned flow content';
        $auth->add($setOwnFlowContent);

        // Content
        $setContent = $auth->createPermission('setContent');
        $setContent->description = 'Manage all content';
        $auth->add($setContent);

        // Upload
        $upload = $auth->createPermission('upload');
        $upload->description = 'Upload content';
        $auth->add($upload);

        /*
         * Roles
         */

        $contentCreator = $auth->createRole('Content creator');
        $contentCreator->data = ['requireFlow' => true];
        $auth->add($contentCreator);
        $auth->addChild($contentCreator, $upload);
        $auth->addChild($contentCreator, $setOwnFlowContent);

        $screenManager = $auth->createRole('Screen manager');
        $auth->add($screenManager);
        $auth->addChild($screenManager, $setFlowContent);
        $auth->addChild($screenManager, $setFlows);
        $auth->addChild($screenManager, $setTemplates);
        $auth->addChild($screenManager, $setScreens);
        $auth->addChild($screenManager, $previewScreen);
        $auth->addChild($screenManager, $contentCreator);

        $admin = $auth->createRole('Administrator');
        $auth->add($admin);
        $auth->addChild($admin, $administration);
        $auth->addChild($admin, $setDevices);
        $auth->addChild($admin, $setContent);
        $auth->addChild($admin, $screenManager);

        $auth->assign($admin, 'admin');
    }
}
