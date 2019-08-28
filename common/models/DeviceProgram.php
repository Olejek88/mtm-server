<?php

namespace common\models;

use common\components\MtmActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "device_program".
 *
 * @property int $_id
 * @property string $uuid
 * @property string $oid
 * @property string $title
 * @property string $period_title1 Закат. Период от заката до конца вечерних сумерек.
 * @property int $time1
 * @property int $value1
 * @property string $period_title2 Конец вечерних сумерек. Период с конца вечерних сумерек до например полуночи.
 * @property int $time2
 * @property int $value2
 * @property string $period_title3 Ночь. Период например с полуночи до трёх часов ночи.
 * @property int $time3
 * @property int $value3
 * @property string $period_title4 Начало утренних сумерек. Период с утренних сумерек до восхода.
 * @property int $time4
 * @property int $value4
 * @property string $createdAt
 * @property string $changedAt
 */
class DeviceProgram extends MtmActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%device_program}}';
    }

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
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uuid', 'oid'], 'required'],
            [['time1', 'time2', 'time3', 'time4'], 'integer', 'min' => 0, 'max' => 100],
            [['value1', 'value2', 'value3', 'value4'], 'integer', 'min' => 0, 'max' => 100],
            [['createdAt', 'changedAt'], 'safe'],
            [
                [
                    'uuid',
                    'oid',
                    'title',
                    'period_title1',
                    'period_title2',
                    'period_title3',
                    'period_title4',
                ],
                'string', 'max' => 45
            ],
            [['uuid'], 'unique'],
            [
                ['oid', 'title'],
                'unique',
                'targetAttribute' => ['oid', 'title'],
                'message' => 'Такое название программы уже существует!',
            ],
            [['oid'], 'checkOrganizationOwn'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'Id',
            'uuid' => 'Uuid',
            'oid' => 'Oid',
            'title' => 'Название программы',
            'period_title1' => 'Закат. Период от заката до конца вечерних сумерек.',
            'time1' => 'Длительность периода в процентах от длительности ночи.',
            'value1' => 'Яркость освещения в процентах',
            'period_title2' => 'Конец вечерних сумерек. Период с конца вечерних сумерек до например полуночи.',
            'time2' => 'Длительность периода в процентах от длительности ночи.',
            'value2' => 'Яркость освещения в процентах',
            'period_title3' => 'Ночь. Период например с полуночи до трёх часов ночи.',
            'time3' => 'Длительность периода в процентах от длительности ночи.',
            'value3' => 'Яркость освещения в процентах',
            'period_title4' => 'Начало утренних сумерек. Период с утренних сумерек до восхода.',
            'time4' => 'Длительность периода в процентах от длительности ночи.',
            'value4' => 'Яркость освещения в процентах',
            'createdAt' => 'Created At',
            'changedAt' => 'Changed At',
        ];
    }
}
