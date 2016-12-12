<?php

use yii\db\Migration;

class m161212_083223_add_contenttype_management_role extends Migration
{
    public function up()
    {
        \Yii::$app->runAction('rbac/add-content-types', ['interactive' => false]);
    }

    public function down()
    {
        echo "m161212_083223_add_contenttype_management_role cannot be reverted.\n";

        return false;
    }
}
