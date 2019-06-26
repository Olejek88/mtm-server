<?php

use yii\db\Migration;

/**
 * Class m190626_085924_remove_mesureType
 */
class m190626_085924_remove_mesureType extends Migration
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

        $this->dropForeignKey('fk-measure-measureTypeUuid', '{{%measure}}');
        $this->dropColumn('{{%measure}}', 'measureTypeUuid');

        $soundFile = '{{%sound_file}}';
        $this->createTable($soundFile, [
            '_id' => $this->primaryKey()->comment("Id"),
            'uuid' => $this->string(45)->unique()->notNull(),
            'oid' => $this->string(45)->notNull(),
            'title' => $this->string(150)->notNull(),
            'soundFile' => $this->string(512)->notNull(),
            'nodeUuid' => $this->string(45)->notNull(),
            'deleted' => $this->smallInteger()->defaultValue(0),
            'createdAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex(
            'idx-sound_files-oid',
            $soundFile,
            'nodeUuid'
        );

        $this->addForeignKey(
            'fk-sound_files-oid',
            $soundFile,
            'oid',
            '{{%organisation}}',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        $this->createIndex(
            'idx-sound_files-nodeUuid',
            $soundFile,
            'nodeUuid'
        );

        $this->addForeignKey(
            'fk-sound_files-nodeUuid',
            $soundFile,
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
        echo "m190626_085924_remove_mesureType cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190626_085924_remove_mesureType cannot be reverted.\n";

        return false;
    }
    */
}
