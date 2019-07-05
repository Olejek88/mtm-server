<?php

namespace common\models;

use common\components\MtmActiveRecord;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * This is the model class for table "object".
 *
 * @property integer $_id
 * @property string $uuid
 * @property string $oid идентификатор организации
 * @property string $title
 * @property string $houseUuid
 * @property string $createdAt
 * @property string $changedAt
 * @property double $latitude
 * @property double $longitude
 * @property string $objectTypeUuid
 * @property boolean $deleted
 *
 * @property House $house
 * @property string $address
 * @property string $fullTitle
 * @property Organisation $organisation
 * @property ObjectType $objectType
 */
class Objects extends MtmActiveRecord
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
        return 'object';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uuid', 'objectTypeUuid', 'houseUuid', 'title'], 'required'],
            [['createdAt', 'changedAt'], 'safe'],
            [['deleted'], 'boolean'],
            [['uuid', 'title', 'objectTypeUuid', 'houseUuid', 'oid'], 'string', 'max' => 50],
            [['latitude', 'longitude'], 'double'],
            [['oid'], 'checkOrganizationOwn'],
        ];
    }

    public function fields1()
    {
        return [
            '_id',
            'uuid',
            'title',
            'objectTypeUuid',
            'objectType' => function ($model) {
                return $model->objectType;
            },
            'houseUuid',
            'house' => function ($model) {
                return $model->house;
            },
            'latitude', 'longitude',
            'createdAt',
            'changedAt',
        ];
    }

    public function getHouse()
    {
        return $this->hasOne(House::class, ['uuid' => 'houseUuid']);
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
            'objectTypeUuid' => Yii::t('app', 'Тип объекта'),
            'objectType' => Yii::t('app', 'Тип объекта'),
            'houseUuid' => Yii::t('app', 'Дом'),
            'house' => Yii::t('app', 'Дом'),
            'latitude' => Yii::t('app', 'Широта'),
            'longitude' => Yii::t('app', 'Долгота'),
            'createdAt' => Yii::t('app', 'Создан'),
            'changedAt' => Yii::t('app', 'Изменен'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getObjectType()
    {
        return $this->hasOne(ObjectType::class, ['uuid' => 'objectTypeUuid']);
    }

    public function getFullTitle()
    {
        return 'ул.' . $this->house->street->title . ', д.' . $this->house->number;
    }

    public function getAddress()
    {
        return 'ул.' . $this->house->street->title . ', д.' . $this->house->number . ' ' . $this->title;
    }

    public function getOrganisation()
    {
        return $this->hasOne(Organisation::class, ['uuid' => 'oid']);
    }
}
