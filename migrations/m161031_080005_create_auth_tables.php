<?php

use yii\db\Migration;
use yii\base\InvalidConfigException;

/**
 * Handles the creation of all auth tables.
 */
// @codingStandardsIgnoreLine
class m161031_080005_create_auth_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $authManager = \Yii::$app->getAuthManager();

        if (!$authManager) {
            throw new InvalidConfigException('You should configure "authManager" component to use database before executing this migration.');
        }

        if (!$authManager->db->schema->getTableSchema($authManager->assignmentTable)) {
            //throw new InvalidConfigException('You should run "yii migrate --migrationPath=@yii/rbac/migrations/" before this.');
            \Yii::$app->runAction('migrate', ['migrationPath' => '@yii/rbac/migrations/', 'interactive' => false]);
        }

        if (!$authManager->db->createCommand('SELECT * FROM '.$authManager->assignmentTable)->queryOne()) {
            //throw new InvalidConfigException('You should run "yii rbac/init" before this.');
            \Yii::$app->runAction('rbac/init', ['interactive' => false]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        return false;
    }
}
