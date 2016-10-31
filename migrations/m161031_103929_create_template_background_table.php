<?php

use yii\db\Migration;

/**
 * Handles the creation of table `template_background`.
 */
// @codingStandardsIgnoreLine
class m161031_103929_create_template_background_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('template_background', [
            'id' => $this->primaryKey()->notNull()->append('AUTO_INCREMENT'),
            'webpath' => $this->string(256)->notNull(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('template_background');
    }
}
