<?php

namespace common\models;

use common\components\MtmActiveRecord;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\ActiveRecord;
use yii\base\InvalidConfigException;

/**
 * This is the model class for table "measure".
 *
 * @property integer $_id
 * @property string $oid идентификатор организации
 * @property string $uuid
 * @property string $sensorChannelUuid
 * @property double $value
 * @property string $date
 * @property string $createdAt
 * @property string $changedAt
 *
 * @property SensorChannel $sensorChannel
 */
class Measure extends MtmActiveRecord
{

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
        return 'measure';
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
                    'value',
                    'date'
                ],
                'required'
            ],
            [['value'], 'number'],
            [['uuid', 'sensorChannelUuid', 'date', 'oid'], 'string', 'max' => 50],
            [['oid', 'createdAt', 'changedAt'], 'safe'],
            [['changedAt'], 'string', 'on' => self::SCENARIO_CUSTOM_UPDATE],
            [['oid'], 'checkOrganizationOwn'],
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
            'sensorChannel' => Yii::t('app', 'Канал измерения'),
            'sensorChannelUuid' => Yii::t('app', 'Канал измерения'),
            'value' => Yii::t('app', 'Значение'),
            'date' => Yii::t('app', 'Дата'),
            'createdAt' => Yii::t('app', 'Создан'),
            'changedAt' => Yii::t('app', 'Изменен'),
        ];
    }

    /**
     * Fields
     *
     * @return array
     */
    public function fields1()
    {
        return ['_id', 'uuid',
            'sensorChannelUuid',
            'sensorChannel' => function ($model) {
                return $model->sensorChannel;
            },
            'value',
            'date',
            'createdAt',
            'changedAt',
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
    public function getSensorChannel()
    {
        return $this->hasOne(SensorChannel::class, ['uuid' => 'sensorChannelUuid']);
    }

    /**
     * @param $sensorChannelUuid
     * @param $startDate
     * @param $endDate
     * @return ActiveRecord
     * @throws InvalidConfigException
     */
    public static function getLastMeasureBetweenDates($sensorChannelUuid, $startDate, $endDate)
    {
        $model = Measure::find()->where(["sensorChannelUuid" => $sensorChannelUuid])
            ->andWhere('date >= "' . $startDate . '"')
            ->andWhere('date < "' . $endDate . '"')
            ->orderBy('date DESC')
            ->one();
        return $model;
    }

}
