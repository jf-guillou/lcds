<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user_has_flow`.
 */
// @codingStandardsIgnoreLine
class m161031_093304_create_user_has_flow_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('user_has_flow', [
            'user_username' => $this->string(64)->notNull(),
            'flow_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey(
            'pk_user_has_flow',
            'user_has_flow',
            ['user_username', 'flow_id']
        );

        $this->createIndex(
            'fk_user_has_flow_flow1_idx',
            'user_has_flow',
            'flow_id'
        );

        $this->createIndex(
            'fk_user_has_flow_user1_idx',
            'user_has_flow',
            'user_username'
        );

        $this->addForeignKey(
            'fk_user_has_flow_user1',
            'user_has_flow',
            'user_username',
            'user',
            'username',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_user_has_flow_flow1',
            'user_has_flow',
            'flow_id',
            'flow',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('user_has_flow');
    }
}
