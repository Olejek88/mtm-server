<?php

use yii\db\Migration;

/**
 * Class m190630_135307_fix_pkey4exchange
 */
class m190630_135307_fix_pkey4exchange extends Migration
{
    const CAMERA = '{{%camera}}';
    const DEVICE = '{{%device}}';
    const SENSOR_CHANNEL = '{{%sensor_channel}}';
    const MEASURE = '{{%measure}}';
    const DEVICE_REGISTER = '{{%device_register}}';
    const THREADS = '{{%threads}}';
    const SENSOR_CONFIG = '{{%sensor_config}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn(self::CAMERA, '_id', $this->integer()->unsigned()->notNull());
        $this->dropPrimaryKey('PRIMARY', self::CAMERA);
        $this->addPrimaryKey('', self::CAMERA, ['_id', 'uuid']);

        $this->alterColumn(self::DEVICE, '_id', $this->integer()->unsigned()->notNull());
        $this->dropPrimaryKey('PRIMARY', self::DEVICE);
        $this->addPrimaryKey('', self::DEVICE, ['_id', 'uuid']);

        $this->alterColumn(self::SENSOR_CHANNEL, '_id', $this->integer()->unsigned()->notNull());
        $this->dropPrimaryKey('PRIMARY', self::SENSOR_CHANNEL);
        $this->addPrimaryKey('', self::SENSOR_CHANNEL, ['_id', 'uuid']);

        $this->alterColumn(self::MEASURE, '_id', $this->integer()->unsigned()->notNull());
        $this->dropPrimaryKey('PRIMARY', self::MEASURE);
        $this->addPrimaryKey('', self::MEASURE, ['_id', 'uuid']);

        $this->alterColumn(self::DEVICE_REGISTER, '_id', $this->integer()->unsigned()->notNull());
        $this->dropPrimaryKey('PRIMARY', self::DEVICE_REGISTER);
        $this->addPrimaryKey('', self::DEVICE_REGISTER, ['_id', 'uuid']);

        $this->alterColumn(self::THREADS, '_id', $this->integer()->unsigned()->notNull());
        $this->dropPrimaryKey('PRIMARY', self::THREADS);
        $this->addPrimaryKey('', self::THREADS, ['_id', 'uuid']);

        $this->alterColumn(self::SENSOR_CONFIG, '_id', $this->integer()->unsigned()->notNull());
        $this->dropPrimaryKey('PRIMARY', self::SENSOR_CONFIG);
        $this->addPrimaryKey('', self::SENSOR_CONFIG, ['_id', 'uuid']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190630_135307_fix_pkey4exchange cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190630_135307_fix_pkey4exchange cannot be reverted.\n";

        return false;
    }
    */
}
