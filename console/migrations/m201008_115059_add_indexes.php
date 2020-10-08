<?php

use yii\db\Migration;

/**
 * Class m201008_115059_add_indexes
 */
class m201008_115059_add_indexes extends Migration
{
    const DEV_REGISTER = '{{%device_register}}';
    const MEASURE = '{{%measure}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('dev_register-date', self::DEV_REGISTER, ['date']);
        $this->createIndex('dev_register-devuuid-date', self::DEV_REGISTER, ['deviceUuid', 'date']);

        $this->createIndex('measure-scuuid-date-idx', self::MEASURE, ['sensorChannelUuid', 'date']);
        $this->createIndex('measure-scuuid-t-p-d-idx', self::MEASURE, ['sensorChannelUuid', 'type', 'parameter', 'date']);
        $this->createIndex('measure-createdat-idx', self::MEASURE, ['createdAt']);
        $this->createIndex('measure-scuuid-createdat-idx', self::MEASURE, ['sensorChannelUuid', 'createdAt']);
        $this->createIndex('measure-changedat-idx', self::MEASURE, ['changedAt']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201008_115059_add_indexes cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201008_115059_add_indexes cannot be reverted.\n";

        return false;
    }
    */
}
