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
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    public $name;
    public $username;
    public $password;
    public $status = User::STATUS_ACTIVE;
    public $role = \common\models\User::ROLE_OPERATOR;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'trim', 'on' => ['default', 'update']],
            ['username', 'required', 'on' => ['default', 'update']],
            ['username', 'unique', 'targetClass' => '\common\models\User',
                'message' => 'This username has already been taken.', 'on' => ['default']],
            ['username', 'unique', 'targetClass' => '\common\models\User',
                'message' => 'This username has already been taken.', 'on' => ['update'],
                'when' => function ($model) {
                    /** @var $model User */
                    $user = \common\models\User::find()->where(['_id' => Yii::$app->request->get('id')])->one();
                    if ($user != null && $user->username == $model->username) {
                        return false;
                    } else {
                        return true;
                    }
                }],
            ['username', 'string', 'min' => 2, 'max' => 255, 'on' => ['default', 'update']],

            ['name', 'trim', 'on' => ['default', 'update']],
            ['name', 'required', 'on' => ['default', 'update']],
            ['name', 'string', 'min' => 2, 'max' => 255, 'on' => ['default', 'update']],

            ['password', 'required', 'on' => ['default']],
            ['password', 'string', 'min' => 6, 'on' => ['default']],
            ['password', 'string', 'min' => 6, 'on' => ['update'], 'skipOnEmpty' => true],

            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED], 'on' => ['default', 'update']],

            [['role'], 'required', 'on' => ['default', 'update']],
            [['role'], 'string', 'max' => 128, 'on' => ['default', 'update']],
            [['role'], 'in', 'range' => [\common\models\User::ROLE_ADMIN, \common\models\User::ROLE_OPERATOR], 'strict' => true, 'on' => ['default', 'update']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Имя',
            'username' => 'Логин',
            'password' => 'Пароль',
            'status' => 'Статус',
            'role' => 'Права',
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

        if ($user->save()) {
            $am = Yii::$app->getAuthManager();
            $roleItem = $am->getRole($this->role);
            $am->assign($roleItem, $user->_id);
            return $user;
        } else {
            return null;
        }
    }


    /**
     * Signs user up.
     *
     * @param $user \common\models\User
     * @return \common\models\User|null the saved model or null if saving fails
     * @throws Exception
     */
    public function update($user)
    {
        if (!$this->validate()) {
            return null;
        }

        $user->username = $this->username;
        $user->name = $this->name;
        if ($this->password != '') {
            $user->setPassword($this->password);
        }

        $user->status = $this->status;

        $am = Yii::$app->getAuthManager();
        $assignments = $am->getAssignments($user->_id);
        foreach ($assignments as $role => $value) {
            $roleItem = $am->getRole($role);
            $am->revoke($roleItem, $user->_id);
        }

        $roleItem = $am->getRole($this->role);
        $am->assign($roleItem, $user->_id);
        $user->type = $this->role == \common\models\User::ROLE_ADMIN ? 0 : 1;

        return $user->save() ? $user : null;
    }
}
