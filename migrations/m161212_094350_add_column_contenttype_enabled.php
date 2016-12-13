<?php

use yii\db\Migration;

/**
 * Handles the addition of column 'enabled' to table `content_type`.
 */
// @codingStandardsIgnoreLine
class m161212_094350_add_column_contenttype_enabled extends Migration
{
    public function up()
    {
        $this->addColumn('content_type', 'enabled', $this->boolean()->notNull()->defaultValue(true));
    }

    public function down()
    {
        $this->dropColumn('content_type', 'enabled');
    }
}
