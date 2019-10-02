<?php

use yii\db\Migration;

/**
 * Class m191001_070438_add_group_control
 */
class m191001_070438_add_group_control extends Migration
{
    const GROUP_CONTROL = '{{%group_control}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(self::GROUP_CONTROL, [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'oid' => $this->string(45)->notNull(),
            'groupUuid' => $this->string(45)->notNull(),
            'date' => $this->timestamp(),
            'type' => $this->integer(),
            'createdAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191001_070438_add_group_control cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191001_070438_add_group_control cannot be reverted.\n";

        return false;
    }
    */
}
