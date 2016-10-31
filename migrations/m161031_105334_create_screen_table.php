<?php

use yii\db\Migration;

/**
 * Handles the creation of table `screen`.
 */
// @codingStandardsIgnoreLine
class m161031_105334_create_screen_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('screen', [
            'id' => $this->integer()->notNull()->append('AUTO_INCREMENT'),
            'name' => $this->string(64)->notNull(),
            'description' => $this->string(1024),
            'template_id' => $this->integer()->notNull(),
            'duration' => $this->integer()->notNull()->defaultValue(60),
            'last_changes' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->addPrimaryKey(
            'pk_screen',
            'screen',
            ['id']
        );

        $this->createIndex(
            'fk_screen_template1_idx',
            'screen',
            'template_id'
        );

        $this->addForeignKey(
            'fk_screen_template1',
            'screen',
            'template_id',
            'screen_template',
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
        $this->dropTable('screen');
    }
}
