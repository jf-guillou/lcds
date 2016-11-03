<?php

use yii\db\Migration;

/**
 * Handles the creation of table `device_has_screen`.
 */
// @codingStandardsIgnoreLine
class m161031_110401_create_device_has_screen_table extends Migration
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

        $this->createTable('device_has_screen', [
            'device_id' => $this->integer()->notNull(),
            'screen_id' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->addPrimaryKey(
            'pk_device_has_screen',
            'device_has_screen',
            ['device_id', 'screen_id']
        );

        $this->createIndex(
            'fk_device_has_screen_screen1_idx',
            'device_has_screen',
            'screen_id'
        );

        $this->createIndex(
            'fk_device_has_screen_device1_idx',
            'device_has_screen',
            'device_id'
        );

        $this->addForeignKey(
            'fk_device_has_screen_screen1',
            'device_has_screen',
            'screen_id',
            'screen',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_device_has_screen_device1',
            'device_has_screen',
            'device_id',
            'device',
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
        $this->dropTable('device_has_screen');
    }
}
