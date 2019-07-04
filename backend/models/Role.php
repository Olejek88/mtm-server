<?php
/**
 * Created by PhpStorm.
 * User: koputo
 * Date: 2/12/19
 * Time: 6:46 PM
 */

namespace backend\models;

use yii\base\Model;
use common\models\User;

class Role extends Model
{
    public $role;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['role'], 'string', 'max' => 128],
            ['role', 'in', 'range' => [
                User::ROLE_ADMIN,
                User::ROLE_OPERATOR,
            ],
                'strict' => true],
        ];
    }
}