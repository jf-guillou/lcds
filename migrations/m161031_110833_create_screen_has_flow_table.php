<?php

use yii\db\Migration;

/**
 * Handles the creation of table `screen_has_flow`.
 */
// @codingStandardsIgnoreLine
class m161031_110833_create_screen_has_flow_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('screen_has_flow', [
            'screen_id' => $this->integer()->notNull(),
            'flow_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey(
            'pk_screen_has_flow',
            'screen_has_flow',
            ['screen_id', 'flow_id']
        );

        $this->createIndex(
            'fk_screen_has_flow_screen1_idx',
            'screen_has_flow',
            'screen_id'
        );

        $this->createIndex(
            'fk_screen_has_flow_flow1_idx',
            'screen_has_flow',
            'flow_id'
        );

        $this->addForeignKey(
            'fk_screen_has_flow_screen1',
            'screen_has_flow',
            'screen_id',
            'screen',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_screen_has_flow_flow1',
            'screen_has_flow',
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
        $this->dropTable('screen_has_flow');
    }
}
