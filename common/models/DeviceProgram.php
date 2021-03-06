<?php

namespace common\models;

use common\components\MtmActiveRecord;
use yii\base\InvalidConfigException;
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
 * @property int $value1
 * @property string $period_title2 Конец вечерних сумерек. Период с конца вечерних сумерек до например полуночи.
 * @property int $time2
 * @property int $value2
 * @property string $period_title3 Ночь. Период например с полуночи до трёх часов ночи.
 * @property int $time3
 * @property int $value3
 * @property string $period_title4 Ночь. Период например с трёх часов ночи до утренних сумерек.
 * @property int $time4
 * @property int $value4
 * @property string $period_title5 Начало утренних сумерек. Период с утренних сумерек до восхода.
 * @property int $value5
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
            [['time2', 'time3', 'time4'], 'integer', 'min' => 0, 'max' => 100],
            [['time2', 'time3', 'time4'], function ($attribute, $params, $validator) {
                if (($this->time2 + $this->time3 + $this->time4) > 100) {
                    $this->addError($attribute, 'Сумма длительностей периодов ночи не должна превышать 100%');
                }
            }],
            [['value1', 'value2', 'value3', 'value4', 'value5'], 'integer', 'min' => 0, 'max' => 100],
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
                    'period_title5',
                ],
                'string', 'max' => 45
            ],
            [['title'], 'checkUseProgram'],
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
            'period_title1' => 'Закат. (Период от заката до конца вечерних сумерек)',
            'value1' => 'Яркость освещения в процентах',
            'period_title2' => 'Конец вечерних сумерек. (Период с конца вечерних сумерек до например полуночи)',
            'time2' => 'Длительность периода в процентах от длительности ночи.',
            'value2' => 'Яркость освещения в процентах',
            'period_title3' => 'Ночь. (Период например с полуночи до трёх часов ночи)',
            'time3' => 'Длительность периода в процентах от длительности ночи.',
            'value3' => 'Яркость освещения в процентах',
            'period_title4' => 'Ночь. (Период например с трёх часов ночи до утренних сумерек)',
            'time4' => 'Длительность периода в процентах от длительности ночи.',
            'value4' => 'Яркость освещения в процентах',
            'period_title5' => 'Начало утренних сумерек. (Период с утренних сумерек до восхода)',
            'value5' => 'Яркость освещения в процентах',
            'createdAt' => 'Создана',
            'changedAt' => 'Изменена',
        ];
    }

    /**
     * @param $attr
     * @param $param
     * @throws InvalidConfigException
     * @throws InvalidConfigException
     */
    public function checkUseProgram($attr, $param)
    {
        $oldValue = $this->getOldAttribute($attr);
        if (!$this->isNewRecord) {
            $dirtyValue = $this->getDirtyAttributes([$attr]);
            if (count($dirtyValue) == 0) {
                return;
            }

            if ($oldValue === $dirtyValue[$attr]) {
                return;
            }
        }

        $lights = DeviceConfig::find()->where(['parameter' => DeviceConfig::PARAM_LIGHT_PROGRAM, 'value' => $oldValue])->all();
        if (count($lights) > 0) {
            $this->addError($attr, 'Программу нельзя переименовать, она используется светильниками.');
        }
    }

}
