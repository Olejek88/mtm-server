<?php

namespace common\models;

use common\components\MtmActiveRecord;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "lost_light".
 *
 * @property integer $_id
 * @property string $oid
 * @property string $uuid
 * @property string $date
 * @property string $title
 * @property string $status
 * @property string $macAddress
 * @property string $deviceUuid
 * @property string $nodeUuid
 * @property string $objectAddress
 * @property string $nodeAddress
 * @property string $createdAt
 * @property string $changedAt
 *
 * @property string $objectFullAddress
 * @property string $nodeFullAddress
 */
class LostLight extends MtmActiveRecord
{

    /**
     * Table name.
     *
     * @return string
     */
    public static function tableName()
    {
        return 'lost_light';
    }

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
     * Rules.
     *
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'oid',
                    'uuid',
                    'date',
                    'title',
                    'status',
                    'macAddress',
                    'deviceUuid',
                    'nodeUuid',
                    'objectAddress',
                    'nodeAddress',
                ],
                'required'
            ],
            [['createdAt', 'changedAt'], 'safe'],
            [
                [
                    'oid',
                ],
                'string', 'max' => 45,
            ],
            [
                [
                    'uuid',
                    'deviceUuid',
                    'nodeUuid',
                ],
                'string', 'max' => 36,
            ],
            [
                [
                    'title',
                    'macAddress',
                ],
                'string', 'max' => 150,
            ],
            [
                [
                    'date',
                    'status',
                ],
                'string', 'max' => 64,
            ],
            [
                [
                    'objectAddress',
                    'nodeAddress',
                ],
                'string', 'max' => 512,
            ],
            [['changedAt'], 'string', 'on' => self::SCENARIO_CUSTOM_UPDATE],
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
            'oid' => Yii::t('app', 'Uuid организации'),
            'date' => Yii::t('app', 'Дата'),
            'title' => Yii::t('app', 'Название'),
            'status' => Yii::t('app', 'Статус'),
            'macAddress' => Yii::t('app', 'MAC адрес'),
            'deviceUuid' => Yii::t('app', 'UUID оборудования'),
            'nodeUuid' => Yii::t('app', 'UUID шкафа установки'),
            'objectAddress' => Yii::t('app', 'Адрес установки'),
            'nodeAddress' => Yii::t('app', 'Адрес шкафа'),
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
     * Адрес объекта установки
     *
     * @return string
     * @throws InvalidConfigException
     */
    function getObjectFullAddress(): string
    {
        /** @var $device Device */
        $device = Device::find()->where(['uuid' => $this->deviceUuid])->limit(1)->one();
        return $device->object->getAddress() . '(' . $device->object->title . ')';
    }

    /**
     * Адрес шкафа
     *
     * @return string
     * @throws InvalidConfigException
     */
    function getNodeFullAddress(): string
    {
        /** @var $node Node */
        $node = Node::find()->where(['uuid' => $this->nodeUuid])->limit(1)->one();
        $address = $node->object->getAddress();
        $address .= $node->object->house->houseTypeUuid == HouseType::HOUSE_TYPE_NO_NUMBER ? ('(' . $node->object->title . ')') : '';
        return $address;
    }
}
