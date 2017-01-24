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
        $this->delete('content_type', ['id' => 'Media']);
    }

    public function down()
    {
        $this->insert('content_type', ['id' => 'Media', 'enabled' => false]);
    }
}
