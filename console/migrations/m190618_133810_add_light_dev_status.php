<?php

use yii\db\Migration;

/**
 * Class m190618_133810_add_light_dev_status
 */
class m190618_133810_add_light_dev_status extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        // ограничиваем уникальность адресов устройств в рамках одной организации
//        $this->createIndex('idx-device_oid-address', '{{%device}}', ['oid', 'address'], true);

        // ограничиваем уникальность адресов шкафов в рамках одной организации
//        $this->createIndex('idx-node_oid-address', '{{%node}}', ['oid', 'address'], true);

        $this->createTable('{{%light_status}}', [
            'oid' => $this->string(45)->notNull(),
            'deviceUuid' => $this->string(45)->notNull(),
            'date' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'address' => $this->string(150)->notNull(),
            'alert0' => $this->boolean()->defaultValue(0),
            'alert1' => $this->boolean()->defaultValue(0),
            'alert2' => $this->boolean()->defaultValue(0),
            'alert3' => $this->boolean()->defaultValue(0),
            'alert4' => $this->boolean()->defaultValue(0),
            'alert5' => $this->boolean()->defaultValue(0),
            'alert6' => $this->boolean()->defaultValue(0),
            'alert7' => $this->boolean()->defaultValue(0),
            'alert8' => $this->boolean()->defaultValue(0),
            'alert9' => $this->boolean()->defaultValue(0),
            'alert10' => $this->boolean()->defaultValue(0),
            'alert11' => $this->boolean()->defaultValue(0),
            'alert12' => $this->boolean()->defaultValue(0),
            'alert13' => $this->boolean()->defaultValue(0),
            'alert14' => $this->boolean()->defaultValue(0),
            'alert15' => $this->boolean()->defaultValue(0),
            'sensor0' => $this->integer()->defaultValue(0),
            'sensor1' => $this->integer()->defaultValue(0),
            'sensor2' => $this->integer()->defaultValue(0),
            'sensor3' => $this->integer()->defaultValue(0),
            'sensor4' => $this->integer()->defaultValue(0),
            'sensor5' => $this->integer()->defaultValue(0),
            'sensor6' => $this->integer()->defaultValue(0),
            'sensor7' => $this->integer()->defaultValue(0),
            'sensor8' => $this->integer()->defaultValue(0),
            'sensor9' => $this->integer()->defaultValue(0),
            'sensor10' => $this->integer()->defaultValue(0),
            'sensor11' => $this->integer()->defaultValue(0),
            'sensor12' => $this->integer()->defaultValue(0),
            'sensor13' => $this->integer()->defaultValue(0),
            'sensor14' => $this->integer()->defaultValue(0),
            'sensor15' => $this->integer()->defaultValue(0),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->addPrimaryKey('pidx', '{{%light_status}}', ['oid', 'deviceUuid']);

        $this->addForeignKey(
            'fk-light_status-oid-organization-uuid',
            '{{%light_status}}',
            'oid',
            '{{%organisation}}',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->addForeignKey(
            'fk-light_status-deviceUuid-device-uuid',
            '{{%light_status}}',
            'deviceUuid',
            '{{%device}}',
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
        echo "m190618_133810_add_light_dev_status cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190618_133810_add_light_dev_status cannot be reverted.\n";

        return false;
    }
    */
}
