<?php

use yii\db\Migration;

/**
 * Class m190618_132058_link2organisation
 */
class m190618_132058_link2organisation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tables = [
            'camera',
            'city',
            'device',
            'device_register',
            'house',
            'measure',
            'message',
            'node',
            'object',
            'sensor_channel',
            'sensor_config',
            'street',
        ];


        foreach ($tables as $table) {
            $this->addForeignKey(
                'fk-' . $table . '-oid-organization-uuid',
                '{{%' . $table . '}}',
                'oid',
                '{{%organisation}}',
                'uuid',
                $delete = 'RESTRICT',
                $update = 'CASCADE'
            );
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190618_132058_link2organisation cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190618_132058_link2organisation cannot be reverted.\n";

        return false;
    }
    */
}
