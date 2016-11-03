<?php

use yii\db\Migration;

/**
 * Handles the creation of table `field`.
 */
// @codingStandardsIgnoreLine
class m161031_104500_create_field_table extends Migration
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

        $this->createTable('field', [
            'id' => $this->primaryKey()->notNull()->append('AUTO_INCREMENT'),
            'template_id' => $this->integer()->notNull(),
            'x1' => $this->float()->notNull(),
            'y1' => $this->float()->notNull(),
            'x2' => $this->float()->notNull(),
            'y2' => $this->float()->notNull(),
            'css' => $this->text(),
            'js' => $this->text(),
        ], $tableOptions);

        $this->createIndex(
            'fk_field_template1_idx',
            'field',
            'template_id'
        );

        $this->addForeignKey(
            'fk_field_template1',
            'field',
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
        $this->dropTable('field');
    }
}
