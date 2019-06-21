<?php

use yii\db\Migration;
use common\models\User;

/**
 * Class m190614_063556_link_user2organization
 */
class m190614_063556_link_user2organization extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%user}}', 'oid', $this->string(45)->notNull());
        $this->insert('{{%organisation}}', [
            'uuid' => User::ORGANISATION_UUID,
            'title' => 'Base',
        ]);

        $this->update('{{%user}}', [
            'oid' => User::ORGANISATION_UUID,
        ]);

        $this->addForeignKey(
            'fk-user-oid-organization-uuid',
            '{{%user}}',
            'oid',
            '{{%organisation}}',
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
        echo "m190614_063556_link_user2organization cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190614_063556_link_user2organization cannot be reverted.\n";

        return false;
    }
    */
}
