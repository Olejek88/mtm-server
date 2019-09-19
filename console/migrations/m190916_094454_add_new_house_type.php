<?php

use common\models\HouseType;
use common\models\ObjectType;
use yii\db\Migration;

/**
 * Class m190916_094454_add_new_house_type
 */
class m190916_094454_add_new_house_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $currentTime = date('Y-m-d\TH:i:s');
        $this->insertIntoType('house_type', HouseType::HOUSE_TYPE_NO_NUMBER,
            'Без дома', $currentTime, $currentTime);
        $row = $this->db->createCommand("select * from object_type where uuid='" . ObjectType::OBJECT_TYPE_PILLAR . "'")->query();
        if ($row->count() == 0) {
            $this->insertIntoType('object_type', ObjectType::OBJECT_TYPE_PILLAR,
                'Столб освещения', $currentTime, $currentTime);
        }
    }

    private function insertIntoType($table, $uuid, $title, $createdAt, $changedAt)
    {
        $this->insert($table, [
            'uuid' => $uuid,
            'title' => $title,
            'createdAt' => $createdAt,
            'changedAt' => $changedAt
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190916_094454_add_new_house_type cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190916_094454_add_new_house_type cannot be reverted.\n";

        return false;
    }
    */
}
