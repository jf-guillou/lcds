<?php

use yii\db\Migration;

/**
 * Handles the creation of table `flow`.
 */
// @codingStandardsIgnoreLine
class m161031_092433_create_flow_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('flow', [
            'id' => $this->integer()->primaryKey()->append('AUTO_INCREMENT'),
            'name' => $this->string(64)->notNull(),
            'description' => $this->string(1024),
            'parent_id' => $this->integer(),

        ], $tableOptions);

        $this->addPrimaryKey(
            'pk_flow',
            'flow',
            ['id']
        );

        $this->createIndex(
            'fk_flow_flow1_idx',
            'flow',
            'parent_id'
        );

        $this->addForeignKey(
            'fk_flow_flow1',
            'flow',
            'parent_id',
            'flow',
            'id',
            'SET NULL',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('flow');
    }
}
