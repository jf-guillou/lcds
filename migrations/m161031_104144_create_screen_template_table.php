<?php

use yii\db\Migration;

/**
 * Handles the creation of table `screen_template`.
 */
// @codingStandardsIgnoreLine
class m161031_104144_create_screen_template_table extends Migration
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

        $this->createTable('screen_template', [
            'id' => $this->primaryKey()->notNull()->append('AUTO_INCREMENT'),
            'name' => $this->string(64)->notNull(),
            'background_id' => $this->integer(),
            'css' => $this->text(),
        ], $tableOptions);

        $this->createIndex(
            'fk_screen_template_template_background1_idx',
            'screen_template',
            'background_id'
        );

        $this->addForeignKey(
            'fk_screen_template_template_background1',
            'screen_template',
            'background_id',
            'template_background',
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
        $this->dropTable('screen_template');
    }
}
