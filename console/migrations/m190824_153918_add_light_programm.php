<?php

use yii\db\Migration;

/**
 * Class m190824_153918_add_light_programm
 */
class m190824_153918_add_light_programm extends Migration
{
    const DEVICE_PROGRAM = '{{%device_program}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(self::DEVICE_PROGRAM, [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'oid' => $this->string(45)->notNull(),
            'title' => $this->string(45)->notNull()->defaultValue('Программа'),
            'period_title1' => $this->string(45)->notNull()->defaultValue('Вечерние сумерки')
                ->comment('Закат. Период от заката до конца вечерних сумерек.'),
            'time1' => $this->integer()->defaultValue(0),
            'value1' => $this->integer()->defaultValue(0),
            'period_title2' => $this->string(45)->notNull()->defaultValue('Вечер')
                ->comment('Конец вечерних сумерек. Период с конца вечерних сумерек до например полуночи.'),
            'time2' => $this->integer()->defaultValue(0),
            'value2' => $this->integer()->defaultValue(0),
            'period_title3' => $this->string(45)->notNull()->defaultValue('Ночь')
                ->comment('Ночь. Период например с полуночи до трёх часов ночи.'),
            'time3' => $this->integer()->defaultValue(0),
            'value3' => $this->integer()->defaultValue(0),
            'period_title4' => $this->string(45)->notNull()->defaultValue('Утренние сумерки')
                ->comment('Начало утренних сумерек. Период с утренних сумерек до восхода.'),
            'time4' => $this->integer()->defaultValue(0),
            'value4' => $this->integer()->defaultValue(0),
            'createdAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        $this->createIndex('oid_title', self::DEVICE_PROGRAM, ['oid', 'title'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190824_153918_add_light_programm cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190824_153918_add_light_programm cannot be reverted.\n";

        return false;
    }
    */
}
