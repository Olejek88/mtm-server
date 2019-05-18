<?php

use common\models\User;
use common\models\Users;
use console\yii2\Migration;

/**
 * Class m190412_142135_insert_references_
 */

class m190412_142135_insert_references_ extends Migration
{
    /**
     * {@inheritdoc}
     */
    const AUTH_KEY = 'K4g2d-bTENTHzzAJp22G1yF6otaUj9EF';

    public function safeUp()
    {
        $currentTime = date('Y-m-d\TH:i:s');

        $this->insert('{{%user}}', [
            '_id' => '1',
            'uuid' => '041DED21-D211-4C0B-BCD6-02E392654332',
            'username' => 'dev',
            'oid' => User::ORGANISATION_UUID,
            'auth_key' => 'f1elprxfre3ri79clcY2VcaBdPqhPLZQ',
            'password_hash' => '$2y$13$nGZaF9DU5t/v63X./MM3Gu/eg0HsXBRtnBZ7adA3spSbJUKtLIEbC',
            'email' => 'shtrmvk@gmail.com',
            'status' => '10',
            'type' => 1,
            'name' => 'Олег Иванов',
            'whoIs' => 'Ведущий инженер',
            'contact' => '+79227000293 Олег',
            'created_at' => $currentTime,
            'updated_at' => $currentTime
        ]);

        $this->insertIntoType('device_status','E681926C-F4A3-44BD-9F96-F0493712798D',
            'В порядке', $currentTime, $currentTime);
        $this->insertIntoType('device_status','D5D31037-6640-4A8B-8385-355FC71DEBD7',
            'Неисправно', $currentTime, $currentTime);
        $this->insertIntoType('device_status','A01B7550-4211-4D7A-9935-80A2FC257E92',
            'Отсутствует', $currentTime, $currentTime);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190412_142135_insert_references_ cannot be reverted.\n";

        return true;
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
        echo "m190412_142135_insert_references_ cannot be reverted.\n";

        return false;
    }
    */
}
