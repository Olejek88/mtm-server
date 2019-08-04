<?php
namespace common\models;

use common\components\MtmActiveRecord;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * This is the model class for table "node".
 *
 * @property integer $_id
 * @property string $uuid
 * @property string $oid идентификатор организации
 * @property string $address
 * @property string $deviceStatusUuid
 * @property string $objectUuid
 * @property string $lastDate
 * @property boolean $security
 * @property string $phone
 * @property string $software
 * @property string $createdAt
 * @property string $changedAt
 * @property boolean $deleted
 *
 * @property ActiveQuery $deviceStatus
 * @property Organisation $organisation
 * @property Object $object
 */
class Node extends MtmActiveRecord
{

    /**
     * Behaviors.
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'createdAt',
                'updatedAtAttribute' => 'changedAt',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * Table name.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'node';
    }

    /**
     * Свойства объекта со связанными данными.
     *
     * @return array
     */
    public function fields1()
    {
        return ['_id', 'uuid', 'oid', 'lastDate', 'security', 'phone', 'software',
            'objectUuid',
            'object' => function ($model) {
                return $model->object;
            },
            'deviceStatusUuid',
            'deviceStatus' => function ($model) {
                return $model->deviceStatus;
            },
            'address', 'deleted', 'createdAt', 'changedAt'
        ];
    }

    /**
     * Rules.
     *
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'uuid',
                    'objectUuid',
                    'deviceStatusUuid',
                ],
                'required'
            ],
            [['address', 'oid', 'createdAt', 'changedAt'], 'safe'],
            [['deleted'], 'boolean'],
            [
                [
                    'uuid',
                    'objectUuid',
                    'deviceStatusUuid',
                    'address'
                ],
                'string', 'max' => 50
            ],
            [['oid'], 'checkOrganizationOwn'],
        ];
    }

    /**
     * Метки для свойств.
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            '_id' => Yii::t('app', '№'),
            'uuid' => Yii::t('app', 'Uuid'),
            'deviceStatusUuid' => Yii::t('app', 'Статус'),
            'deviceStatus' => Yii::t('app', 'Статус'),
            'objectUuid' => Yii::t('app', 'Объект'),
            'object' => Yii::t('app', 'Объект'),
            'address' => Yii::t('app', 'Адрес'),
            'software' => Yii::t('app', 'Версия ПО'),
            'phone' => Yii::t('app', 'Телефон'),
            'lastDate' => Yii::t('app', 'Дата последней связи'),
            'security' => Yii::t('app', 'Охрана'),
            'createdAt' => Yii::t('app', 'Создан'),
            'changedAt' => Yii::t('app', 'Изменен'),
        ];
    }

    /**
     * Проверка целостности модели?
     *
     * @return bool
     */
    public function upload()
    {
        if ($this->validate()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Объект связанного поля.
     *
     * @return ActiveQuery
     */
    public function getDeviceStatus()
    {
        return $this->hasOne(
            DeviceStatus::class, ['uuid' => 'deviceStatusUuid']
        );
    }

    /**
     * Объект связанного поля.
     *
     * @return ActiveQuery
     */
    public function getObject()
    {
        return $this->hasOne(Objects::class, ['uuid' => 'objectUuid']);
    }

    public function getOrganisation()
    {
        return $this->hasOne(Organisation::class, ['uuid' => 'oid']);
    }
}
