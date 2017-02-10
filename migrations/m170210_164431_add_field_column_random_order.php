<?php

use yii\db\Migration;

/**
 * Handles the addition of column 'random_order' to table `field`.
 */
// @codingStandardsIgnoreLine
class m170210_164431_add_field_column_random_order extends Migration
{
    public function up()
    {
        $this->addColumn('field', 'random_order', $this->boolean()->notNull()->defaultValue(true));
    }

    public function down()
    {
        $this->dropColumn('field', 'random_order');
    }
}
