<?php

namespace common\models;

use common\components\MtmActiveRecord;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "device".
 *
 * @property integer $_id
 * @property string $uuid
 * @property string $oid идентификатор организации
 * @property string $name
 * @property string $address
 * @property string $deviceTypeUuid
 * @property string $serial
 * @property string $port
 * @property integer $interface
 * @property string $deviceStatusUuid
 * @property string $date
 * @property string $nodeUuid
 * @property string $objectUuid
 * @property string $createdAt
 * @property string $changedAt
 * @property boolean $deleted
 * @property integer $linkTimeout
 *
 * @property Objects $object
 * @property int $num
 * @property DeviceStatus $deviceStatus
 * @property DeviceType $deviceType
 * @property string $fullTitle
 * @property Node $node
 * @property SensorChannel[] $sensorChannels
 * @property DeviceProgram $deviceProgram
 */
class Device extends MtmActiveRecord
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
     * Table name.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'device';
    }

    /**
     * Свойства объекта со связанными данными.
     *
     * @return array
     */
    public function fields1()
    {
        return ['_id', 'uuid', 'address', 'name',
            'nodeUuid',
            'node' => function ($model) {
                return $model->node;
            },
            'objectUuid',
            'object' => function ($model) {
                return $model->object;
            },
            'deviceTypeUuid',
            'deviceType' => function ($model) {
                return $model->deviceType;
            },
            'deviceStatusUuid',
            'deviceStatus' => function ($model) {
                return $model->deviceStatus;
            },
            'serial', 'date', 'port', 'deleted', 'interface',
            'createdAt', 'changedAt'
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
                    'nodeUuid',
                    'deviceTypeUuid',
                    'deviceStatusUuid',
                    'objectUuid',
                    'serial',
                    'interface',
                    'port',
                ],
                'required'
            ],
            [['date', 'oid', 'createdAt', 'changedAt'], 'safe'],
            [['changedAt'], 'string', 'on' => self::SCENARIO_CUSTOM_UPDATE],
            [['deleted'], 'boolean'],
            [
                [
                    'uuid',
                    'name',
                    'deviceTypeUuid',
                    'deviceStatusUuid',
                    'objectUuid',
                    'serial',
                    'port',
                    'nodeUuid',
                ],
                'string', 'max' => 50
            ],
            [['address'], 'string', 'min' => 16, 'max' => 16, 'when' => function ($model) {
                /** @var Device $model */
                if (in_array($model->deviceTypeUuid, [DeviceType::DEVICE_LIGHT, DeviceType::DEVICE_ZB_COORDINATOR])) {
                    return true;
                } else {
                    return false;
                }
            }],
            [['address'], 'checkUniqueAddress',
                'when' => function ($model) {
                    /** @var Device $model */
                    if (in_array($model->deviceTypeUuid, [DeviceType::DEVICE_LIGHT, DeviceType::DEVICE_ZB_COORDINATOR])) {
                        return true;
                    } else {
                        return false;
                    }
                }],
            ['address', 'filter', 'filter' => 'strtoupper', 'when' => function ($model) {
                /** @var Device $model */
                if (in_array($model->deviceTypeUuid, [DeviceType::DEVICE_LIGHT, DeviceType::DEVICE_ZB_COORDINATOR])) {
                    return true;
                } else {
                    return false;
                }
            }],
            [['interface'], 'integer'],
            [['oid'], 'checkOrganizationOwn'],
            [['linkTimeout'], 'integer'],
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
            'name' => Yii::t('app', 'Название'),
            'interface' => Yii::t('app', 'Интерфейс'),
            'deviceTypeUuid' => Yii::t('app', 'Тип оборудования'),
            'deviceType' => Yii::t('app', 'Тип'),
            'date' => Yii::t('app', 'Дата последней связи'),
            'deviceStatusUuid' => Yii::t('app', 'Статус'),
            'deviceStatus' => Yii::t('app', 'Статус'),
            'objectUuid' => Yii::t('app', 'Объект'),
            'object' => Yii::t('app', 'Объект'),
            'nodeUuid' => Yii::t('app', 'Шкаф установки'),
            'node' => Yii::t('app', 'Шкаф установки'),
            'port' => Yii::t('app', 'Порт'),
            'serial' => Yii::t('app', 'Серийный номер'),
            'address' => Yii::t('app', 'Адрес'),
            'linkTimeout' => Yii::t('app', 'Таймаут до потери связи'),
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
    public function getDeviceType()
    {
        return $this->hasOne(
            DeviceType::class, ['uuid' => 'deviceTypeUuid']
        );
    }

    /**
     * Объект связанного поля.
     *
     * @return ActiveQuery
     */
    public function getNode()
    {
        return $this->hasOne(Node::class, ['uuid' => 'nodeUuid']);
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

    /**
     * Объект связанного поля.
     *
     * @return string
     */
    public function getFullTitle()
    {
        return $this->object->getFullTitle() . ' [' . $this->name . ']';
    }

    public static function getFullTitleStatic($device)
    {
        return Objects::getFullTitleStatic($device['object']) . ' [' . $device['name'] . ']';
    }

    /**
     * Объект связанного поля.
     *
     * @return ActiveQuery
     */
    public function getSensorChannels()
    {
        return $this->hasMany(SensorChannel::class, ['deviceUuid' => 'uuid']);
    }

    /**
     * Объект связанного поля.
     *
     * @param $measureTypeUuid
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public function getSensorChannel($measureTypeUuid)
    {
        return SensorChannel::find()->where(['deviceUuid' => $this->uuid, 'measureTypeUuid' => $measureTypeUuid]);
    }

    /**
     * @return array|DeviceProgram|ActiveRecord|null
     * @throws InvalidConfigException
     */
    public function getDeviceProgram()
    {
        $config = DeviceConfig::find()->where(['deviceUuid' => $this->uuid, 'parameter' => 'Программа'])->limit(1)->one();
        if ($config != null) {
            return DeviceProgram::find()->where(['title' => $config->value])->limit(1)->one();
        } else {
            return null;
        }
    }

    /**
     * @param $attr
     * @param $param
     * @throws InvalidConfigException
     */
    public function checkUniqueAddress($attr, $param)
    {
        if (!$this->isNewRecord) {
            $dirtyValue = $this->getDirtyAttributes([$attr]);
            if (count($dirtyValue) == 0) {
                return;
            }

            $oldValue = $this->getOldAttribute($attr);
            if ($oldValue === $dirtyValue[$attr]) {
                return;
            }
        }

        $existDevices = Device::find()->where([$attr => $this->$attr, 'deviceTypeUuid' => [DeviceType::DEVICE_LIGHT, DeviceType::DEVICE_ZB_COORDINATOR]])->all();
        if (count($existDevices) > 0) {
            $this->addError($attr, 'Это адрес уже занят.');
        }
    }
}
