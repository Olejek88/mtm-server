<?php

use yii\db\Migration;

/**
 * Class m190805_101447_add_new_fields
 */
class m190805_101447_add_new_fields extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%measure}}', 'parameter', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190805_101447_add_new_fields cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190805_101447_add_new_fields cannot be reverted.\n";

        return false;
    }
    */
}
