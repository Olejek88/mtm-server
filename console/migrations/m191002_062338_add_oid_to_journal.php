<?php

use common\models\User;
use yii\db\Migration;

/**
 * Class m191002_062338_add_oid_to_journal
 */
class m191002_062338_add_oid_to_journal extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%journal}}', 'oid', $this->string(45)->notNull());
        $this->update('{{%journal}}', [
            'oid' => User::ORGANISATION_UUID,
        ]);
        $this->addForeignKey(
            'fk-journal-oid-organization-uuid',
            '{{%journal}}',
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
        echo "m191002_062338_add_oid_to_journal cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191002_062338_add_oid_to_journal cannot be reverted.\n";

        return false;
    }
    */
}
