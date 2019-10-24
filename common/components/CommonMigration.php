<?php

namespace common\components;

use yii\db\Connection;
use yii\db\Exception;

class CommonMigration
{
    /**
     * Создаёт список фиксированных групп для организации
     *
     * @param $db Connection
     * @param $oid
     * @throws Exception
     */
    public static function createGroups($db, $oid)
    {
        for ($i = 0; $i < 16; $i++) {
            $db->createCommand()->insert('{{%group}}', [
                'uuid' => MainFunctions::GUID(),
                'oid' => $oid,
                'title' => 'Группа #' . $i,
                'groupId' => $i,
            ])->execute();
        }
    }
}