<?php

use yii\db\Migration;

/**
 * Class m190619_050659_add_new_fields
 */
class m190619_050659_add_new_fields extends Migration
{
    const NODE = '{{%node}}';
    const MESSAGE = '{{%message}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::MESSAGE, 'nodeUuid', $this->string(45));
        $this->createIndex(
            'idx-nodeUuid',
            self::MESSAGE,
            'nodeUuid'
        );

        $this->addForeignKey(
            'fk-message-nodeUuid',
            self::MESSAGE,
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
        echo "m190619_050659_add_new_fields cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190619_050659_add_new_fields cannot be reverted.\n";

        return false;
    }
    */
}
