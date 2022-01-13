<?php

use common\models\HouseType;
use yii\db\Migration;

/**
 * Class m220113_102004_add_house_type
 */
class m220113_102004_add_house_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $currentTime = date('Y-m-d\TH:i:s');
        $this->insert('{{%house_type}}', [
            'uuid' => HouseType::HOUSE_TYPE_NUMBER,
            'title' => 'Дом',
            'createdAt' => $currentTime,
            'changedAt' => $currentTime,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220113_102004_add_house_type cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220113_102004_add_house_type cannot be reverted.\n";

        return false;
    }
    */
}
