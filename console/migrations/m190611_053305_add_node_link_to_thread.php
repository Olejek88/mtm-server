<?php

use yii\db\Migration;

/**
 * Class m190611_053305_add_node_link_to_thread
 */
class m190611_053305_add_node_link_to_thread extends Migration
{
    const NODE = '{{%node}}';
    const THREADS = '{{%threads}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::THREADS, 'nodeUuid', $this->string(45));
        $this->createIndex(
            'idx-nodeUuid',
            self::THREADS,
            'nodeUuid'
        );

        $this->addForeignKey(
            'fk-threads-nodeUuid',
            self::THREADS,
            'nodeUuid',
            self::NODE,
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
        $this->dropColumn(self::THREADS,'nodeUuid');
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190611_053305_add_node_link_to_thread cannot be reverted.\n";

        return false;
    }
    */
}
