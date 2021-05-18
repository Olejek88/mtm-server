<?php

use yii\db\Migration;
use common\models\User;

/**
 * Class m190702_114744_fix_role
 */
class m190702_114744_fix_role extends Migration
{
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function safeUp()
    {
        $am = Yii::$app->getAuthManager();

        $role = $am->getRole(User::ROLE_ADMIN);
        $role->description = 'Администратор';
        $am->update(User::ROLE_ADMIN, $role);
        $role = $am->getRole(User::ROLE_OPERATOR);
        $role->description = 'Оператор';
        $am->update(User::ROLE_OPERATOR, $role);
/*
        $role = $am->getRole('analyst');
        $am->removeChildren($role);
        $am->remove($role);
        $perm = $am->getPermission('permissionAnalyst');
        $am->remove($perm);

        $role = $am->getRole('user');
        $am->removeChildren($role);
        $am->remove($role);
        $perm = $am->getPermission('permissionUser');
        $am->remove($perm);
*/
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190702_114744_fix_role cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190702_114744_fix_role cannot be reverted.\n";

        return false;
    }
    */
}
