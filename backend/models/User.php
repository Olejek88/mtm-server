<?php

namespace backend\models;

use common\components\MainFunctions;
use Yii;
use yii\base\Model;
use Exception;

/**
 * Signup form
 */
class User extends Model
{
    public $name;
    public $username;
    public $password;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['name', 'trim'],
            ['name', 'required'],
            ['name', 'string', 'min' => 2, 'max' => 255],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Signs user up.
     *
     * @return \common\models\User|null the saved model or null if saving fails
     * @throws Exception
     */
    public function save()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new \common\models\User();
        $user->username = $this->username;
        $user->email = 'email@' . time() . '.ru';
        $user->oid = \common\models\User::getOid(Yii::$app->user->identity);
        $user->setPassword($this->password);
        $user->uuid = MainFunctions::GUID();
        $user->name = $this->name;
        $user->type = '0';
        $user->contact = 'нет';
        $user->generateAuthKey();

        return $user->save() ? $user : null;
    }
}
