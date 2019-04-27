<?php

namespace api\controllers;

use api\components\BaseController;
use common\models\Device;
use common\models\User;
use yii\db\ActiveRecord;

class EquipmentController extends BaseController
{
    /** @var ActiveRecord $modelClass */
    public $modelClass = Device::class;

    public function actionIndex()
    {
        $req = \Yii::$app->request;
        $user = \Yii::$app->user;
        $users = User::findIdentity($user->id)->users;

        /** @var ActiveRecord $class */
        $class = $this->modelClass;
        $query = $class::find();

        // проверяем параметры запроса
        $uuid = $req->getQueryParam('uuid');
        if ($uuid != null) {
            $query->andWhere(['`equipment`.`uuid`' => $uuid]);
        }

        $changedAfter = $req->getQueryParam('changedAfter');
        if ($changedAfter != null) {
            $query->andWhere(['>=', '`equipment`.`changedAt`', $changedAfter]);
        }

        // проверяем что хоть какие-то условия были заданы
        if ($query->where == null) {
            return [];
        }

        // выбираем данные из базы
        $query->select('equipment.*')
            ->leftJoin('flat', '`equipment`.`flatUuid` = `flat`.`uuid`')
            ->leftJoin('user_house', '`flat`.`houseUuid` = `user_house`.`houseUuid`')
            ->andWhere(['`user_house`.`userUuid`' => $users->uuid]);
        $result = $query->asArray()->all();

        return $result;
    }

    /**
     * @return array
     */
    public function actionCreate()
    {
        return parent::createBase();
    }
}
