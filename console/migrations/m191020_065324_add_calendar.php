<?php

use common\components\CommonMigration;
use common\models\Organisation;
use yii\db\Migration;

/**
 * Class m191020_065324_add_calendar
 */
class m191020_065324_add_calendar extends Migration
{
    /**
     * {@inheritdoc}
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        // удаляем настройки программ для всех устройств, так как программы теперь будут связанны с группами
        $this->delete('{{%device_config}}', ['parameter' => 'Программа']);

        // удаляем текущие связки светильников с группами
        $this->delete('{{%device_group}}');

        // удаляем записи из календаря для групп
        $this->delete('{{%group_control}}');

        // удаляем текущие группы
        $this->delete('{{%group}}');

        // добавляем поле с фиксированным номером группы
        $this->addColumn('{{group}}', 'groupId', $this->integer()->notNull());

        // добавляем связь с программой управления
        $this->addColumn('{{group}}', 'deviceProgramUuid', $this->string(45)->null());
        $this->addForeignKey(
            'fk-group_deviceProgramUuid-program_uuid',
            '{{%group}}',
            'deviceProgramUuid',
            '{{%device_program}}',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        // Создаём жестко заданные группы
        $orgs = Organisation::find()->all();
        foreach ($orgs as $org) {
            CommonMigration::createGroups($this->db, $org->uuid);
        }

        // создаём уникальный ключ для связки oid, groupUuid, deviceUuid для того чтобы одно устройство могло быть
        // только в одной группе
        $this->createIndex('idx-device_group-oid-groupUuid-deviceUuid', '{{%device_group}}',
            ['oid', 'groupUuid', 'deviceUuid'], true);


        // добавляем связь с программой управления для календаря групп
        $this->addColumn('{{group_control}}', 'deviceProgramUuid', $this->string(45)->null());
        $this->addForeignKey(
            'fk-group_control_deviceProgramUuid-program_uuid',
            '{{%group_control}}',
            'deviceProgramUuid',
            '{{%device_program}}',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        // таблица для календарей шкафов
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%node_control}}', [
            '_id' => $this->primaryKey(),
            'uuid' => $this->string(45)->notNull()->unique(),
            'oid' => $this->string(45)->notNull(),
            'nodeUuid' => $this->string(45)->notNull(),
            'date' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'type' => $this->integer(),
            'createdAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'changedAt' => $this->dateTime()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
        ], $tableOptions);

        // создаём уникальный ключ для связки oid, nodeUuid, date, type для того чтобы
        // для одного шкафа была только одна запись с восходом/закатом на указанное время
        $this->createIndex('idx-node_control-oid-nodeUuid-date-type', '{{%node_control}}',
            ['oid', 'nodeUuid', 'date', 'type'], true);

        // связь с организацией
        $this->addForeignKey(
            'fk-node_control-oid-organization-uuid',
            '{{%node_control}}',
            'oid',
            '{{%organisation}}',
            'uuid',
            $delete = 'RESTRICT',
            $update = 'CASCADE'
        );

        // связь со шкафом
        $this->addForeignKey(
            'fk-node_control-nodeUuid-organization-uuid',
            '{{%node_control}}',
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
        echo "m191020_065324_add_calendar cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191020_065324_add_calendar cannot be reverted.\n";

        return false;
    }
    */
}
