<?php

use yii\db\Migration;

/**
 * Handles the creation of table `content_type`.
 */
// @codingStandardsIgnoreLine
class m161031_094144_create_content_type_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('content_type', [
            'id' => $this->string(45)->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey(
            'pk_content_type',
            'content_type',
            ['id']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('content_type');
    }
}
