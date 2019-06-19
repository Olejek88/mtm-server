<?php

namespace common\models;

use common\components\MtmActiveRecord;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "light_status".
 *
 * @property string $oid
 * @property string $deviceUuid
 * @property string $date
 * @property string $address
 * @property int $alert0
 * @property int $alert1
 * @property int $alert2
 * @property int $alert3
 * @property int $alert4
 * @property int $alert5
 * @property int $alert6
 * @property int $alert7
 * @property int $alert8
 * @property int $alert9
 * @property int $alert10
 * @property int $alert11
 * @property int $alert12
 * @property int $alert13
 * @property int $alert14
 * @property int $alert15
 * @property int $sensor0
 * @property int $sensor1
 * @property int $sensor2
 * @property int $sensor3
 * @property int $sensor4
 * @property int $sensor5
 * @property int $sensor6
 * @property int $sensor7
 * @property int $sensor8
 * @property int $sensor9
 * @property int $sensor10
 * @property int $sensor11
 * @property int $sensor12
 * @property int $sensor13
 * @property int $sensor14
 * @property int $sensor15
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Device $device
 * @property Organisation $organisation
 */
class LightStatus extends MtmActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%light_status}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['oid', 'deviceUuid', 'address'], 'required'],
            [['date', 'created_at', 'updated_at'], 'safe'],
            [
                [
                    'alert0', 'alert1', 'alert2', 'alert3', 'alert4', 'alert5', 'alert6', 'alert7',
                    'alert8', 'alert9', 'alert10', 'alert11', 'alert12', 'alert13', 'alert14', 'alert15',
                ],
                'boolean'
            ],
            [
                [
                    'sensor0', 'sensor1', 'sensor2', 'sensor3', 'sensor4', 'sensor5', 'sensor6', 'sensor7',
                    'sensor8', 'sensor9', 'sensor10', 'sensor11', 'sensor12', 'sensor13', 'sensor14', 'sensor15'
                ],
                'integer'
            ],
            [['oid', 'deviceUuid'], 'string', 'max' => 45],
            [['address'], 'string', 'max' => 150],
            [['deviceUuid'], 'exist', 'skipOnError' => true, 'targetClass' => Device::class, 'targetAttribute' => ['deviceUuid' => 'uuid']],
            [['oid'], 'exist', 'skipOnError' => true, 'targetClass' => Organisation::class, 'targetAttribute' => ['oid' => 'uuid']],
            [['oid'], 'checkOrganizationOwn']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'oid' => 'Oid',
            'deviceUuid' => 'Device Uuid',
            'date' => 'Date',
            'address' => 'Address',
            'alert0' => 'Alert0',
            'alert1' => 'Alert1',
            'alert2' => 'Alert2',
            'alert3' => 'Alert3',
            'alert4' => 'Alert4',
            'alert5' => 'Alert5',
            'alert6' => 'Alert6',
            'alert7' => 'Alert7',
            'alert8' => 'Alert8',
            'alert9' => 'Alert9',
            'alert10' => 'Alert10',
            'alert11' => 'Alert11',
            'alert12' => 'Alert12',
            'alert13' => 'Alert13',
            'alert14' => 'Alert14',
            'alert15' => 'Alert15',
            'sensor0' => 'Sensor0',
            'sensor1' => 'Sensor1',
            'sensor2' => 'Sensor2',
            'sensor3' => 'Sensor3',
            'sensor4' => 'Sensor4',
            'sensor5' => 'Sensor5',
            'sensor6' => 'Sensor6',
            'sensor7' => 'Sensor7',
            'sensor8' => 'Sensor8',
            'sensor9' => 'Sensor9',
            'sensor10' => 'Sensor10',
            'sensor11' => 'Sensor11',
            'sensor12' => 'Sensor12',
            'sensor13' => 'Sensor13',
            'sensor14' => 'Sensor14',
            'sensor15' => 'Sensor15',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getDevice()
    {
        return $this->hasOne(Device::class, ['uuid' => 'deviceUuid']);
    }

    /**
     * @return ActiveQuery
     */
    public function getOrganisation()
    {
        return $this->hasOne(Organisation::class, ['uuid' => 'oid']);
    }

    /**
     * @param $alert integer (16bit)
     */
    public function setAlerts($alert)
    {
        $alert = $alert & 0xffff;
        for ($idx = 0; $idx < 16; $idx++) {
            $val = 0x0001 << $idx;
            $val = $alert & $val;
            $sensorName = 'alert' . $idx;
            $this->$sensorName = $val > 0 ? true : false;
        }
    }

    /**
     * @param $status array
     */
    public function setStatus($status)
    {
        $idx = 0;
        foreach ($status as $value) {
            $sensorName = 'sensor' . $idx++;
            $this->$sensorName = $value;
        }
    }
}
