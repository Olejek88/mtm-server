<?php

use yii\db\Migration;

/**
 * Class m191203_135017_create_area_node_link
 */
class m191203_135017_create_area_node_link extends Migration
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

        // таблица "территорий"
        $this->createTable('{{%area}}', [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'oid' => $this->string(45)->notNull(),
            'title' => $this->string(128)->notNull(),
            'createdAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        // связь с организацией
        $this->addForeignKey(
            'fk-area-oid-organization-uuid',
            '{{%area}}',
            'oid',
            '{{%organisation}}',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        // таблица для для связи "территориального" расположения со шкафом
        $this->createTable('{{%area_node}}', [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'oid' => $this->string(45)->notNull(),
            'areaUuid' => $this->string(45)->notNull(),
            'nodeUuid' => $this->string(45)->notNull(),
            'createdAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        // создаём уникальный ключ для связки areaUuid, nodeUuid
        // для того чтобы шкаф мог быть связанн только с одной территорией
        $this->createIndex('idx-area_node-areaUuid-nodeUuid', '{{%area_node}}',
            ['areaUuid', 'nodeUuid'], true);

        // связь с организацией
        $this->addForeignKey(
            'fk-area_node-oid-organization-uuid',
            '{{%area_node}}',
            'oid',
            '{{%organisation}}',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        // связь со шкафом
        $this->addForeignKey(
            'fk-area_node-nodeUuid-node-uuid',
            '{{%area_node}}',
            'nodeUuid',
            '{{%node}}',
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
        echo "m191203_135017_create_area_node_link cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191203_135017_create_area_node_link cannot be reverted.\n";

        return false;
    }
    */
}
