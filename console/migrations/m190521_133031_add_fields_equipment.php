<?php

use yii\db\Migration;

/**
 * Class m190521_133031_add_fields_equipment
 */
class m190521_133031_add_fields_equipment extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $currentTime = date('Y-m-d\TH:i:s');

        $this->addColumn('camera', 'address', $this->string(45));
        $this->addColumn('camera', 'port', $this->integer());

        $this->insertIntoType('device_type','0FBACF26-31CA-4B92-BCA3-220E09A6D2D3',
            'Электросчетчик', $currentTime, $currentTime);
        $this->insertIntoType('device_type','CFD3C7CC-170C-4764-9A8D-10047C8B8B1D',
            'Умный светильник', $currentTime, $currentTime);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190521_133031_add_fields_equipment cannot be reverted.\n";

        return false;
    }

    private function insertIntoType($table, $uuid, $title, $createdAt, $changedAt) {
        $this->insert($table, [
            'uuid' => $uuid,
            'title' => $title,
            'createdAt' => $createdAt,
            'changedAt' => $changedAt
        ]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190521_133031_add_fields_equipment cannot be reverted.\n";

        return false;
    }
    */
}
