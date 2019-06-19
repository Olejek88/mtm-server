<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "thread".
 *
 * @property integer $_id
 * @property string $uuid
 * @property string $oid идентификатор организации
 * @property string $title
 * @property string $deviceUuid
 * @property string $port
 * @property integer $speed
 * @property integer $status
 * @property integer $work
 * @property string $deviceTypeUuid
 * @property string $c_time
 * @property string $message
 * @property string $nodeUuid
 * @property string $createdAt
 * @property string $changedAt
 *
 * @property Node $node
 * @property Device $device
 * @property DeviceType $deviceType
 */
class Threads extends ActiveRecord
{
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
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'threads';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uuid', 'title', 'deviceTypeUuid'], 'required'],
            [['c_time', 'message', 'createdAt', 'changedAt'], 'safe'],
            [['speed', 'status', 'work'], 'integer'],
            [['uuid', 'title', 'deviceTypeUuid', 'nodeUuid', 'deviceUuid', 'port', 'oid'], 'string', 'max' => 50],
        ];
    }

    public function fields()
    {
        return [
            '_id',
            'uuid',
            'oid',
            'deviceUuid',
            'device' => function ($model) {
                return $model->device;
            },
            'nodeUuid',
            'node' => function ($model) {
                return $model->node;
            },
            'title',
            'port',
            'speed',
            'status',
            'work',
            'c_time',
            'message',
            'deviceTypeUuid',
            'deviceType' => function ($model) {
                return $model->deviceType;
            },
            'createdAt',
            'changedAt',
        ];
    }

    public function getDevice()
    {
        return $this->hasOne(Device::class, ['uuid' => 'deviceUuid']);
    }

    public function getDeviceType()
    {
        return $this->hasOne(DeviceType::class, ['uuid' => 'deviceTypeUuid']);
    }

    public function getNode()
    {
        return $this->hasOne(Node::class, ['uuid' => 'nodeUuid']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => Yii::t('app', '№'),
            'uuid' => Yii::t('app', 'Uuid'),
            'title' => Yii::t('app', 'Название потока'),
            'device' => Yii::t('app', 'Устройство'),
            'deviceUuid' => Yii::t('app', 'Устройство'),
            'deviceType' => Yii::t('app', 'Тип устройства'),
            'deviceTypeUuid' => Yii::t('app', 'Тип устройства'),
            'node' => Yii::t('app', 'Контроллер'),
            'nodeUuid' => Yii::t('app', 'Контроллер'),
            'port'  => Yii::t('app', 'Порт'),
            'speed' => Yii::t('app', 'Скорость'),
            'status' => Yii::t('app', 'Статус'),
            'work' => Yii::t('app', 'Работа'),
            'c_time' => Yii::t('app', 'Время'),
            'message' => Yii::t('app', 'Сообщение'),
            'createdAt' => Yii::t('app', 'Создан'),
            'changedAt' => Yii::t('app', 'Изменен'),
        ];
    }
}
