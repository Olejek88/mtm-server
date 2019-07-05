<?php

namespace common\models;

use common\components\MtmActiveRecord;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * This is the model class for table "device_register".
 *
 * @property int $_id
 * @property string $uuid
 * @property string $oid идентификатор организации
 * @property string $deviceUuid
 * @property string $date
 * @property string $description
 * @property string $createdAt
 * @property string $changedAt
 *
 * @property Device $device
 */
class DeviceRegister extends MtmActiveRecord
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
                'value' => function () {
                    return $this->scenario == self::SCENARIO_CUSTOM_UPDATE ? $this->changedAt : new Expression('NOW()');
                },
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'device_register';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uuid', 'deviceUuid', 'date'], 'required'],
            [['data'], 'safe'],
            [['uuid', 'deviceUuid', 'oid'], 'string', 'max' => 50],
            [['description'], 'string', 'max' => 250],
            [['createdAt', 'changedAt'], 'safe'],
            [['changedAt'], 'string', 'on' => self::SCENARIO_CUSTOM_UPDATE],
            [['oid'], 'checkOrganizationOwn'],
        ];
    }

    /**
     * Labels.
     *
     * @return array
     *
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uuid' => Yii::t('app', 'Uuid'),
            'deviceUuid' => Yii::t('app', 'Оборудование'),
            'device' => Yii::t('app', 'Оборудование'),
            'description' => Yii::t('app', 'Запись'),
            'date' => Yii::t('app', 'Дата'),
            'createdAt' => Yii::t('app', 'Создан'),
            'changedAt' => Yii::t('app', 'Изменен'),
        ];
    }

    /**
     * Объект связанного поля.
     *
     * @return ActiveQuery
     */
    public function getDevice()
    {
        return $this->hasOne(
            Device::class, ['uuid' => 'deviceUuid']
        );
    }

    public function fields1()
    {
        return ['uuid',
            'device' => function ($model) {
                return $model->device;
            }, 'date', 'description', 'createdAt', 'changedAt'
        ];
    }
}