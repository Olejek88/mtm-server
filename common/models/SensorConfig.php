<?php
namespace common\models;

use common\components\MtmActiveRecord;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * This is the model class for table "sensor_config".
 *
 * @property integer $_id
 * @property string $oid идентификатор организации
 * @property string $uuid
 * @property string $sensorChannelUuid
 * @property string $config
 * @property string $createdAt
 * @property string $changedAt
 *
 * @property SensorChannel $sensorChannel
 */
class SensorConfig extends MtmActiveRecord
{
    // параметр который задаётся для типов измерений COORD_IN1 и COORD_IN2
    public $threshold;

    /**
     * Behaviors
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
     * Название таблицы
     *
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName()
    {
        return 'sensor_config';
    }

    /**
     * Rules
     *
     * @inheritdoc
     *
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'uuid',
                    'sensorChannelUuid',
                    'config',
                ],
                'required'
            ],
            [['oid','createdAt', 'changedAt'], 'safe'],
            [
                [
                    'uuid',
                    'sensorChannelUuid'
                ],
                'string', 'max' => 45
            ],
            [['createdAt', 'changedAt'], 'safe'],
            [['changedAt'], 'string', 'on' => self::SCENARIO_CUSTOM_UPDATE],
            [['oid'], 'checkOrganizationOwn'],
            [['threshold'], 'string'],
        ];
    }

    /**
     * Fields
     *
     * @return array
     */
    public function fields1()
    {
        return ['_id', 'uuid', 'config',
            'sensorChannelUuid',
            'sensorChannel' => function ($model) {
                return $model->sensorChannel;
            },
            'createdAt', 'changedAt'
        ];
    }

    /**
     * Названия отрибутов
     *
     * @inheritdoc
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            '_id' => Yii::t('app', '№'),
            'uuid' => Yii::t('app', 'Uuid'),
            'sensorChannelUuid' => Yii::t('app', 'Канал/устройство'),
            'config' => Yii::t('app', 'Конфигурация'),
            'createdAt' => Yii::t('app', 'Создан'),
            'changedAt' => Yii::t('app', 'Изменен'),
        ];
    }

    /**
     * Объект связанного поля.
     *
     * @return ActiveQuery
     */
    public function getSensorChannel()
    {
        return $this->hasOne(
            SensorChannel::class, ['uuid' => 'sensorChannelUuid']
        );
    }

}
