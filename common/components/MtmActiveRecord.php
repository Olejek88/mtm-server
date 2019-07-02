<?php


namespace common\components;

use common\models\User;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\Application;

class MtmActiveRecord extends ActiveRecord
{

    const SCENARIO_CUSTOM_UPDATE = 'custom_update';

    /**
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public static function find()
    {
        if (Yii::$app instanceof Application) {
            $aq = Yii::createObject(MtmActiveQuery::class, [get_called_class()]);
            $aq->andWhere(['oid' => User::getOid(Yii::$app->user->identity)]);
        } else {
            $aq = Yii::createObject(ActiveQuery::class, [get_called_class()]);
        }

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
        if (Yii::$app instanceof Application) {
            if ($this->attributes[$attr] != User::getOid(Yii::$app->user->identity)) {
                $this->addError($attr, 'Не верный идентификатор организации.');
            }
        } else {
            // TODO: как проверить что создаваемая запись принадлежит той организации которой она должна принадлежать?
        }
    }
}