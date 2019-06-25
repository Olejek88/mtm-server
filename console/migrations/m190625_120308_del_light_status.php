<?php

use yii\db\Migration;

/**
 * Class m190625_120308_del_light_status
 */
class m190625_120308_del_light_status extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropTable('{{%light_status}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190625_120308_del_light_status cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190625_120308_del_light_status cannot be reverted.\n";

        return false;
    }
    */
}
