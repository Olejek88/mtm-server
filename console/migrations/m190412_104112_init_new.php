<?php

use console\yii2\Migration;

/**
 * Class m190412_104112_init_new
 */
class m190412_104112_init_new extends Migration
{
    /**
     * {@inheritdoc}
     */
    const CAMERA = '{{%camera}}';
    const CITY = '{{%city}}';
    const DEVICE = '{{%device}}';
    const DEVICE_REGISTER = '{{%device_register}}';
    const DEVICE_STATUS = '{{%device_status}}';
    const DEVICE_TYPE = '{{%device_type}}';
    const HOUSE = '{{%house}}';
    const HOUSE_TYPE = '{{%house_type}}';
    const JOURNAL = '{{%journal}}';
    const MESSAGE = '{{%message}}';
    const MEASURE = '{{%measure}}';
    const MEASURE_TYPE = '{{%measure_type}}';
    const NODE = '{{%node}}';
    const OBJECT = '{{%object}}';
    const OBJECT_TYPE = '{{%object_type}}';
    const ORGANISATION = '{{%organisation}}';
    const SENSOR_CHANNEL = '{{%sensor_channel}}';
    const SENSOR_CONFIG = '{{%sensor_config}}';
    const STREET = '{{%street}}';
    const USER = '{{%user}}';

    const FK_RESTRICT = 'RESTRICT';
    const FK_CASCADE = 'CASCADE';

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(self::USER, [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'oid' => $this->string()->notNull(),
            'username' => $this->string()->notNull()->unique(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->unique(),
            'email' => $this->string()->notNull()->unique(),

            'type' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'whoIs' => $this->string(45)->defaultValue(""),
            'image' => $this->string(),
            'contact' => $this->string()->notNull(),

            'deleted' => $this->smallInteger()->defaultValue(0),

            'status' => $this->smallInteger()->notNull()->defaultValue(10),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createTable(self::CITY, [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'oid' => $this->string(45)->notNull(),
            'title' => $this->string()->notNull(),
            'createdAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createTable(self::STREET, [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'oid' => $this->string(45),
            'title' => $this->string()->notNull(),
            'cityUuid' => $this->string(45)->notNull(),
            'deleted' => $this->smallInteger()->defaultValue(0),
            'createdAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex(
            'idx-cityUuid',
            self::STREET,
            'cityUuid'
        );

        $this->addForeignKey(
            'fk-street-cityUuid',
            self::STREET,
            'cityUuid',
            self::CITY,
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->createTable(self::HOUSE_TYPE, [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'title' => $this->string()->notNull(),
            'createdAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createTable(self::HOUSE, [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'oid' => $this->string(45)->notNull(),
            'number' => $this->string()->notNull(),
            'houseTypeUuid' => $this->string(45)->notNull(),
            'streetUuid' => $this->string(45)->notNull(),
            'deleted' => $this->smallInteger()->defaultValue(0),
            'createdAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex(
            'idx-streetUuid',
            'house',
            'streetUuid'
        );

        $this->addForeignKey(
            'fk-house-streetUuid',
            'house',
            'streetUuid',
            'street',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->createIndex(
            'idx-houseTypeUuid',
            'house',
            'houseTypeUuid'
        );

        $this->addForeignKey(
            'fk-house-houseTypeUuid',
            'house',
            'houseTypeUuid',
            'house_type',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->createTable(self::OBJECT_TYPE, [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'title' => $this->string()->notNull(),
            'createdAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createTable(self::OBJECT, [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'oid' => $this->string(45)->notNull(),
            'title' => $this->string()->notNull(),
            'houseUuid' => $this->string(45)->notNull(),
            'objectTypeUuid' => $this->string(45)->notNull(),
            'longitude' => $this->double(),
            'latitude' => $this->double(),
            'deleted' => $this->smallInteger()->defaultValue(0),
            'createdAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex(
            'idx-houseUuid',
            'object',
            'houseUuid'
        );

        $this->addForeignKey(
            'fk-object-houseUuid',
            'object',
            'houseUuid',
            'house',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->createIndex(
            'idx-objectTypeUuid',
            'object',
            'objectTypeUuid'
        );

        $this->addForeignKey(
            'fk-object-objectTypeUuid',
            'object',
            'objectTypeUuid',
            'object_type',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->createTable(self::DEVICE_TYPE, [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'title' => $this->string()->notNull(),
            'createdAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createTable(self::DEVICE_STATUS, [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'title' => $this->string()->notNull(),
            'createdAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createTable(
            self::NODE,
            [
                '_id' => $this->primaryKey()->comment("Id"),
                'uuid' => $this->string(45)->unique()->notNull(),
                'oid' => $this->string(45)->notNull(),
                'address' => $this->string(45),
                'deviceStatusUuid' => $this->string(45)->notNull(),
                'objectUuid' => $this->string(45)->notNull(),
                'deleted' => $this->smallInteger()->defaultValue(0),
                'createdAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
                'changedAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            ], $tableOptions
        );

        $this->createIndex(
            'idx-deviceStatusUuid',
            self::NODE,
            'deviceStatusUuid'
        );

        $this->addForeignKey(
            'fk-node-deviceStatusUuid',
            self::NODE,
            'deviceStatusUuid',
            'device_status',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->createIndex(
            'idx-node-objectUuid',
            self::NODE,
            'objectUuid'
        );

        $this->addForeignKey(
            'fk-node-objectUuid',
            self::NODE,
            'objectUuid',
            'object',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        //--------------------------------------------------------------------------------------------------------------
        $this->createTable(
            self::CAMERA,
            [
                '_id' => $this->primaryKey()->comment("Id"),
                'uuid' => $this->string(45)->unique()->notNull(),
                'oid' => $this->string(45)->notNull(),
                'title' => $this->string(150)->notNull(),
                'deviceStatusUuid' => $this->string(45)->notNull(),
                'nodeUuid' => $this->string(45)->notNull(),
                'objectUuid' => $this->string(45)->notNull(),
                'deleted' => $this->smallInteger()->defaultValue(0),
                'createdAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
                'changedAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            ], $tableOptions
        );

        $this->createIndex(
            'idx-deviceStatusUuid',
            self::CAMERA,
            'deviceStatusUuid'
        );

        $this->addForeignKey(
            'fk-camera-deviceStatusUuid',
            self::CAMERA,
            'deviceStatusUuid',
            'device_status',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->createIndex(
            'idx-camera-nodeUuid',
            self::CAMERA,
            'nodeUuid'
        );

        $this->addForeignKey(
            'fk-camera-nodeUuid',
            self::CAMERA,
            'nodeUuid',
            'node',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->createIndex(
            'idx-camera-objectUuid',
            self::CAMERA,
            'objectUuid'
        );

        $this->addForeignKey(
            'fk-camera-objectUuid',
            self::CAMERA,
            'objectUuid',
            'object',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );
        //--------------------------------------------------------------------------------------------------------------
        $this->createTable(self::ORGANISATION, [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'title' => $this->string()->notNull(),
            'deleted' => $this->smallInteger()->defaultValue(0),
            'createdAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);
        //--------------------------------------------------------------------------------------------------------------

        $this->createTable(self::DEVICE, [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'oid' => $this->string(45)->notNull(),
            'address' => $this->string(150)->notNull(),
            'nodeUuid' => $this->string(45)->notNull(),
            'objectUuid' => $this->string(45)->notNull(),
            'deviceTypeUuid' => $this->string(45)->notNull(),
            'deviceStatusUuid' => $this->string(45)->notNull(),
            'port' => $this->string(),
            'serial' => $this->string(),
            'interface' => $this->smallInteger()->defaultValue(1),
            'date' => $this->timestamp()->defaultValue('2019-01-01'),
            'deleted' => $this->smallInteger()->defaultValue(0),
            'createdAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex(
            'idx-nodeUuid',
            self::DEVICE,
            'nodeUuid'
        );

        $this->addForeignKey(
            'fk-device-nodeUuid',
            self::DEVICE,
            'nodeUuid',
            self::NODE,
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->createIndex(
            'idx-deviceTypeUuid',
            self::DEVICE,
            'deviceTypeUuid'
        );

        $this->addForeignKey(
            'fk-device-deviceTypeUuid',
            self::DEVICE,
            'deviceTypeUuid',
            self::DEVICE_TYPE,
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->createIndex(
            'idx-deviceStatusUuid',
            self::DEVICE,
            'deviceStatusUuid'
        );

        $this->addForeignKey(
            'fk-device-deviceStatusUuid',
            self::DEVICE,
            'deviceStatusUuid',
            self::DEVICE_STATUS,
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->createIndex(
            'idx-device-objectUuid',
            self::DEVICE,
            'objectUuid'
        );

        $this->addForeignKey(
            'fk-device-objectUuid',
            self::DEVICE,
            'objectUuid',
            'object',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->createTable(self::DEVICE_REGISTER, [
            '_id' => $this->primaryKey(),
            'oid' => $this->string(45)->notNull(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'deviceUuid' => $this->string(45)->notNull(),
            'date' => $this->timestamp()->defaultValue('2019-01-01'),
            'description' => $this->string(),
            'createdAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex(
            'idx-deviceUuid',
            self::DEVICE_REGISTER,
            'deviceUuid'
        );

        $this->addForeignKey(
            'fk-device_register-deviceUuid',
            self::DEVICE_REGISTER,
            'deviceUuid',
            self::DEVICE,
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        //--------------------------------------------------------------------------------------------------------------
        $this->createTable('{{%journal}}', [
            '_id' => $this->primaryKey(),
            'userUuid' => $this->string(45)->notNull(),
            'date' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'description' => $this->string()->defaultValue("")
        ]);

        $this->createIndex(
            'idx-userUuid',
            'journal',
            'userUuid'
        );

        $this->addForeignKey(
            'fk-journal-userUuid',
            'journal',
            'userUuid',
            'user',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        //--------------------------------------------------------------------------------------------------------------
        $this->createTable('{{%measure_type}}', [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'title' => $this->string()->notNull(),
            'createdAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP')
        ], $tableOptions);

        $this->createTable(self::MEASURE, ['_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'oid' => $this->string(45)->notNull(),
            'measureTypeUuid' => $this->string(45)->notNull(),
            'sensorChannelUuid' => $this->string(45)->notNull(),
            'value' => $this->double(),
            'date' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'createdAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex(
            'idx-sensorChannelUuid',
            self::MEASURE,
            'sensorChannelUuid'
        );

        $this->createTable(self::SENSOR_CHANNEL, [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'oid' => $this->string(45)->notNull(),
            'title' => $this->string()->notNull(),
            'register' => $this->string()->notNull(),
            'deviceUuid' => $this->string(45),
            'measureTypeUuid' => $this->string(45)->notNull(),
            'createdAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex(
            'idx-deviceUuid',
            self::SENSOR_CHANNEL,
            'deviceUuid'
        );

        $this->addForeignKey(
            'fk-sensor_channel-deviceUuid',
            self::SENSOR_CHANNEL,
            'deviceUuid',
            self::DEVICE,
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );


        $this->addForeignKey(
            'fk-measure-sensorChannelUuid',
            'measure',
            'sensorChannelUuid',
            self::SENSOR_CHANNEL,
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->createIndex(
            'idx-measureTypeUuid',
            self::MEASURE,
            'measureTypeUuid'
        );

        $this->addForeignKey(
            'fk-measure-measureTypeUuid',
            self::MEASURE,
            'measureTypeUuid',
            self::MEASURE_TYPE,
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );
        //--------------------------------------------------------------------------------------------------------------

        $this->createTable(self::MESSAGE, [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'oid' => $this->string(45)->notNull(),
            'link' => $this->string()->notNull(),
            'createdAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);


        $this->createTable(self::SENSOR_CONFIG, [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'oid' => $this->string(45)->notNull(),
            'config' => $this->string(),
            'sensorChannelUuid' => $this->string(45)->notNull(),
            'createdAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex(
            'idx-sensorChannelUuid',
            self::SENSOR_CONFIG,
            'sensorChannelUuid'
        );

        $this->addForeignKey(
            'fk-shutdown-sensorChannelUuid',
            self::SENSOR_CONFIG,
            'sensorChannelUuid',
            self::SENSOR_CHANNEL,
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public
    function safeDown()
    {

        return true;
    }
}
