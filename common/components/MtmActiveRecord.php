<?php


namespace common\components;

use common\models\User;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class MtmActiveRecord extends ActiveRecord
{

    /**
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public static function find()
    {
        $aq = Yii::createObject(MtmActiveQuery::class, [get_called_class()]);
        $aq->andWhere(['oid' => User::getOid(Yii::$app->user->identity)]);

        return $aq;
    }

    /**
     * Проверка на принадлежность пользователя указанному идентификатору организации.
     *
     * @param $attr
     * @param $param
     */
    public function checkOrganizationOwn($attr, $param)
    {
        if ($this->attributes[$attr] != User::getOid(Yii::$app->user->identity)) {
            $this->addError($attr, 'Не верный идентификатор организации.');
        }
    }
}