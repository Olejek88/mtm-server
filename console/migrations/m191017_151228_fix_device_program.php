<?php

use yii\db\Migration;

/**
 * Class m191017_151228_fix_device_program
 */
class m191017_151228_fix_device_program extends Migration
{
    /**
     * {@inheritdoc}
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $command = $this->db->createCommand("UPDATE device_program SET period_title4='Утро' WHERE period_title4='Поздняя ночь'");
        $command->execute();
        $this->alterColumn('{{%device_program}}', 'period_title4',
            $this->string(45)->notNull()->defaultValue('Утро'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191017_151228_fix_device_program cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191017_151228_fix_device_program cannot be reverted.\n";

        return false;
    }
    */
}
