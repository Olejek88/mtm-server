<?php

namespace backend\models;

use common\components\CommonMigration;
use common\components\MainFunctions;
use common\models\Organisation;
use common\models\User;
use yii\base\Model;
use Exception;
use Throwable;
use Yii;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $organizationTitle;
    public $organizationInn;


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

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],

            ['organizationTitle', 'trim'],
            ['organizationTitle', 'required'],
            ['organizationTitle', 'string', 'min' => 2, 'max' => 100],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     * @throws Exception
     * @throws Throwable
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $organisation = new Organisation();
        $organisation->uuid = MainFunctions::GUID();
        $organisation->title = $this->organizationTitle;
        if ($organisation->save()) {
            $user = new User();
            $user->username = $this->username;
            $user->email = $this->email;
            $user->setPassword($this->password);
            $user->generateAuthKey();

            $user->uuid = MainFunctions::GUID();
            $user->type = 0;
            $user->status = User::STATUS_ACTIVE;
            $user->name = $user->username;
            $user->contact = 'нет';
            $user->oid = $organisation->uuid;

            if ($user->save()) {
                $am = Yii::$app->getAuthManager();
                $roleAdmin = $am->getRole(User::ROLE_ADMIN);
                $am->assign($roleAdmin, $user->_id);
                // создаём для организации группы
                CommonMigration::createGroups(Yii::$app->db, $organisation->uuid);
                return $user;
            } else {
                $organisation->delete();
                return null;
            }
        } else {
            return null;
        }
    }

    /*
     * Create a random string
     * @author	XEWeb <>
     * @param $length the length of the string to create
     * @return $str the string
     */
    function randomString($length = 6)
    {
        $str = "";
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }
}
