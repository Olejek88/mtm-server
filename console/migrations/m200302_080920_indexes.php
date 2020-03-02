<?php

use yii\db\Migration;

/**
 * Class m200302_080920_indexes
 */
class m200302_080920_indexes extends Migration
{
    const MEASURE = '{{%measure}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx-type',
            self::MEASURE,
            'type'
        );
        $this->createIndex(
            'idx-parameter',
            self::MEASURE,
            'parameter'
        );
        $this->createIndex(
            'idx-date',
            self::MEASURE,
            'date'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200302_080920_indexes cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200302_080920_indexes cannot be reverted.\n";

        return false;
    }
    */
}
