<?php

use yii\db\Migration;

/**
 * Handles the deletion of row 'Media' from table `content_type`.
 */
// @codingStandardsIgnoreLine
class m170124_151514_delete_contenttype_media extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->delete('content_type', ['id' => 'Media']);
    }

    public function down()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->insert('content_type', ['id' => 'Media', 'enabled' => false]);
    }
}
