<?php

namespace common\models;

use common\components\MtmActiveRecord;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * This is the model class for table "device_group".
 *
 * @property integer $_id
 * @property string $uuid
 * @property string $oid идентификатор организации
 * @property string $deviceUuid
 * @property string $groupUuid
 * @property string $createdAt
 * @property string $changedAt
 *
 * @property Group $group
 * @property Device $device
 */
class DeviceGroup extends MtmActiveRecord
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
        return 'device_group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uuid', 'title', 'deviceUuid', 'groupUuid'], 'required'],
            [['oid', 'createdAt', 'changedAt'], 'safe'],
            [['changedAt'], 'string', 'on' => self::SCENARIO_CUSTOM_UPDATE],
            [['uuid', 'deviceUuid', 'groupUuid'], 'string', 'max' => 50],
            [['title'], 'string', 'max' => 100],
            [['oid'], 'checkOrganizationOwn'],
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => Yii::t('app', '№'),
            'uuid' => Yii::t('app', 'Uuid'),
            'title' => Yii::t('app', 'Название'),
            'device' => Yii::t('app', 'Устройство'),
            'deviceUuid' => Yii::t('app', 'Устройство'),
            'group' => Yii::t('app', 'Группа'),
            'groupUuid' => Yii::t('app', 'Группа'),
            'createdAt' => Yii::t('app', 'Создан'),
            'changedAt' => Yii::t('app', 'Изменен'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::class, ['uuid' => 'groupUuid']);
    }

    /**
     * @return ActiveQuery
     */
    public function getDevice()
    {
        return $this->hasOne(Device::class, ['uuid' => 'deviceUuid']);
    }

}
