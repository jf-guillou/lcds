<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user`.
 */
// @codingStandardsIgnoreLine
class m161031_091506_create_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('user', [
            'username' => $this->string(64)->notNull(),
            'hash' => $this->string(64),
            'authkey' => $this->string(64)->notNull(),
            'access_token' => $this->string(64)->notNull(),
            'added_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'last_login_at' => $this->timestamp(),
        ], $tableOptions);

        $this->addPrimaryKey(
            'pk_user',
            'user',
            ['username']
        );

        \app\models\User::create('admin', 'admin');
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('user');
    }
}
