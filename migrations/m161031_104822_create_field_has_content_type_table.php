<?php

use yii\db\Migration;

/**
 * Handles the creation of table `field_has_content_type`.
 */
// @codingStandardsIgnoreLine
class m161031_104822_create_field_has_content_type_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('field_has_content_type', [
            'field_id' => $this->integer()->notNull(),
            'content_type_id' => $this->string(64)->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey(
            'pk_field_has_content_type',
            'field_has_content_type',
            ['field_id', 'content_type_id']
        );

        $this->createIndex(
            'fk_field_has_content_type_field1_idx',
            'field',
            'field_id'
        );

        $this->createIndex(
            'fk_field_has_content_type_content_type1_idx',
            'field',
            'content_type_id'
        );

        $this->addPrimaryKey(
            'fk_field_has_content_type_field1',
            'field_has_content_type',
            'field_id',
            'field',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addPrimaryKey(
            'fk_field_has_content_type_content_type1',
            'field_has_content_type',
            'content_type_id',
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
        $this->dropTable('field_has_content_type');
    }
}
