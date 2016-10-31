<?php

use yii\db\Migration;

/**
 * Handles the creation of table `content`.
 */
// @codingStandardsIgnoreLine
class m161031_094747_create_content_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('content', [
            'id' => $this->integer()->notNull()->append('AUTO_INCREMENT'),
            'name' => $this->string(64)->notNull(),
            'description' => $this->string(1024),
            'flow_id' => $this->integer()->notNull(),
            'type_id' => $this->string(45)->notNull(),
            'data' => $this->text(),
            'duration' => $this->integer()->notNull()->defaultValue(10),
            'start_ts' => $this->timestamp(),
            'end_ts' => $this->timestamp(),
            'add_ts' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'enabled' => $this->boolean()->notNull()->defaultValue(true),
        ], $tableOptions);

        $this->addPrimaryKey(
            'pk_content',
            'content',
            ['id']
        );

        $this->createIndex(
            'fk_content_flow1_idx',
            'content',
            'flow_id'
        );

        $this->createIndex(
            'fk_content_content_type1_idx',
            'content',
            'type_id'
        );

        $this->addForeignKey(
            'fk_content_flow1',
            'content',
            'flow_id',
            'flow',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_content_content_type1',
            'content',
            'type_id',
            'content_type',
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
        $this->dropTable('content');
    }
}
