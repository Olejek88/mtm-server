<?php

use common\models\DeviceType;
use yii\db\Migration;

/**
 * Class m220109_044240_add_e18_zigbee_type
 */
class m220109_044240_add_e18_zigbee_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $currentTime = date('Y-m-d\TH:i:s');
        $this->insert('{{%device_type}}', [
                'uuid' => DeviceType::DEVICE_ZB_COORDINATOR_E18,
                'title' => 'ZigBee координатор E18',
                'createdAt' => $currentTime,
                'changedAt' => $currentTime,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220109_044240_add_e18_zigbee_type cannot be reverted.\n";
        $this->delete('{{%device_type}}', [
                'uuid' => DeviceType::DEVICE_ZB_COORDINATOR_E18,
            ]
        );
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220109_044240_add_e18_zigbee_type cannot be reverted.\n";

        return false;
    }
    */
}
