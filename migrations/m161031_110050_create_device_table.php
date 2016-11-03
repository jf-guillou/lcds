<?php

use yii\db\Migration;

/**
 * Handles the creation of table `device`.
 */
// @codingStandardsIgnoreLine
class m161031_110050_create_device_table extends Migration
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

        $this->createTable('device', [
            'id' => $this->primaryKey()->notNull()->append('AUTO_INCREMENT'),
            'name' => $this->string(64)->notNull(),
            'description' => $this->string(1024),
            'last_auth' => $this->timestamp()->null(),
            'enabled' => $this->boolean()->notNull()->defaultValue(false),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('device');
    }
}
