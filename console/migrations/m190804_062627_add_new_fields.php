<?php

use yii\db\Migration;

/**
 * Class m190804_062627_add_new_fields
 */
class m190804_062627_add_new_fields extends Migration
{
    const DEVICE_GROUP = '{{%sensor_group}}';
    const GROUP = '{{%group}}';
    const DEVICE = '{{%device}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->addColumn('{{%measure}}', 'type', $this->integer()->defaultValue(0));
        $this->addColumn('{{%node}}', 'lastDate', $this->timestamp()->defaultValue('2019-01-01'));
        $this->addColumn('{{%node}}', 'security', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%node}}', 'phone', $this->string()->defaultValue(""));
        $this->addColumn('{{%node}}', 'software', $this->string()->defaultValue("2.0.1"));

        $this->createTable(self::GROUP, [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'oid' => $this->string(45)->notNull(),
            'title' => $this->string(100)->notNull(),
            'createdAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createTable(self::DEVICE_GROUP, [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'oid' => $this->string(45)->notNull(),
            'groupUuid' => $this->string(45)->notNull(),
            'deviceUuid' => $this->string(45)->notNull(),
            'createdAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex(
            'idx-deviceUuid',
            self::DEVICE_GROUP,
            'deviceUuid'
        );

        $this->addForeignKey(
            'fk-device_group-deviceUuid',
            self::DEVICE_GROUP,
            'deviceUuid',
            self::DEVICE,
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->createIndex(
            'idx-groupUuid',
            self::DEVICE_GROUP,
            'groupUuid'
        );

        $this->addForeignKey(
            'fk-device_group-groupUuid',
            self::DEVICE_GROUP,
            'groupUuid',
            self::DEVICE,
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
        echo "m190804_062627_add_new_fields cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190804_062627_add_new_fields cannot be reverted.\n";

        return false;
    }
    */
}
