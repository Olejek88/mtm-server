<?php

use yii\db\Migration;

/**
 * Class m190610_172132_sync_with_controller
 */
class m190610_172132_sync_with_controller extends Migration
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

        $this->addColumn('device', 'name', $this->string(45));
        $this->addColumn('node', 'address', $this->string(45));

        $this->createTable('thread', [
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
            'c_time' => $this->timestamp()->notNull()->defaultValue('0000-00-00 00:00:00'),
            'message' => $this->string(250)->notNull(),
            'createdAt' => $this->timestamp()->notNull()->defaultValue('0000-00-00 00:00:00'),
            'changedAt' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

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
