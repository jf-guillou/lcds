<?php

use yii\db\Migration;

/**
 * Handles the update of foreign key 'screen_template-template_background1' from table `screen_template`.
 */
// @codingStandardsIgnoreLine
class m170210_155249_update_screen_template_fk extends Migration
{
    public function up()
    {
        $this->dropForeignKey(
            'fk_screen_template_template_background1',
            'screen_template'
        );

        $this->addForeignKey(
            'fk_screen_template_template_background1',
            'screen_template',
            'background_id',
            'template_background',
            'id',
            'SET NULL',
            'SET NULL'
        );
    }

    public function down()
    {
        $this->dropForeignKey(
            'fk_screen_template_template_background1',
            'screen_template'
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
}
