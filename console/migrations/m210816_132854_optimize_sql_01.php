<?php

use yii\db\Migration;

/**
 * Class m210816_132854_optimize_sql_01
 */
class m210816_132854_optimize_sql_01 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('register-oid-devUuid-idx', '{{%device_register}}', ['oid', 'deviceUuid']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210816_132854_optimize_sql_01 cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210816_132854_optimize_sql_01 cannot be reverted.\n";

        return false;
    }
    */
}
