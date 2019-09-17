<?php

use common\models\DeviceType;
use yii\db\Migration;

/**
 * Class m190912_152302_add_new_field
 */
class m190912_152302_add_new_field extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%device}}', 'num', $this->integer()->defaultValue(0));
        $date = date('Y-m-d H:i:s');
        $this->insert('{{%device_type}}', [
            'uuid' => DeviceType::DEVICE_LIGHT_WITHOUT_ZB,
            'title' => 'Неуправляемый светильник',
            'createdAt' => $date,
            'changedAt' => $date,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190912_152302_add_new_field cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190912_152302_add_new_field cannot be reverted.\n";

        return false;
    }
    */
}
