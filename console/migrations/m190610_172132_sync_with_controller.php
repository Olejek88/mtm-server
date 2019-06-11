<?php

use yii\db\Migration;

/**
 * Class m190610_172132_sync_with_controller
 */
class m190610_172132_sync_with_controller extends Migration
{
    const DEVICE = '{{%device}}';
    const THREADS = '{{%threads}}';
    const DEVICE_TYPE = '{{%device_type}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->addColumn('device', 'name', $this->string(45));
        $this->createTable('{{%threads}}', [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'oid' => $this->string(45)->notNull(),
            'deviceUuid' => $this->string(45),
            'port' => $this->string(50)->notNull(),
            'speed' => $this->integer()->notNull()->defaultValue(19200),
            'title' => $this->string(150)->notNull(),
            'status' => $this->integer()->notNull()->defaultValue(0),
            'work' => $this->integer()->notNull()->defaultValue(0),
            'deviceTypeUuid' => $this->string(45),
            'c_time' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'message' => $this->string(250)->notNull(),
            'createdAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex(
            'idx-deviceUuid',
            self::THREADS,
            'deviceUuid'
        );

        $this->addForeignKey(
            'fk-threads-deviceUuid',
            self::THREADS,
            'deviceUuid',
            self::DEVICE,
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->createIndex(
            'idx-deviceTypeUuid',
            self::THREADS,
            'deviceTypeUuid'
        );

        $this->addForeignKey(
            'fk-threads-deviceTypeUuid',
            self::THREADS,
            'deviceTypeUuid',
            self::DEVICE_TYPE,
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190610_172132_sync_with_controller cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190610_172132_sync_with_controller cannot be reverted.\n";

        return false;
    }
    */
}
