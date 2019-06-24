<?php
namespace common\models;

use common\components\MtmActiveRecord;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * This is the model class for table "camera".
 *
 * @property integer $_id
 * @property string $uuid
 * @property string $oid идентификатор организации
 * @property string $title
 * @property string $deviceStatusUuid
 * @property string $objectUuid
 * @property string $nodeUuid
 * @property string $address
 * @property string $createdAt
 * @property string $changedAt
 * @property boolean $deleted
 *
 * @property DeviceStatus $deviceStatus
 * @property Objects $object
 * @property Photo $photo
 * @property Organisation $organisation
 * @property Node $node
 */
class Camera extends MtmActiveRecord
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
                'value' => new Expression('NOW()'),
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
        return 'camera';
    }

    /**
     * Свойства объекта со связанными данными.
     *
     * @return array
     */
    public function fields()
    {
        return ['_id', 'uuid', 'title',
            'nodeUuid',
            'node' => function ($model) {
                return $model->node;
            },
            'objectUuid',
            'object' => function ($model) {
                return $model->object;
            },
            'deviceStatusUuid',
            'deviceStatus' => function ($model) {
                return $model->deviceStatus;
            },
            'deleted', 'address','createdAt', 'changedAt'
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
                    'title',
                    'deviceStatusUuid',
                    'nodeUuid',
                    'objectUuid',
                    'address'
                ],
                'required'
            ],
            [['createdAt', 'changedAt'], 'safe'],
            [['deleted'], 'boolean'],
            [
                [
                    'uuid',
                    'deviceStatusUuid',
                    'oid',
                    'nodeUuid',
                    'objectUuid'
                ],
                'string', 'max' => 50
            ],
            [['title'], 'string', 'max' => 150],
            [['oid'], 'checkOrganizationOwn'],
            [['address'], 'string', 'max' => 1024],
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
            'title' => Yii::t('app', 'Название'),
            'deviceStatusUuid' => Yii::t('app', 'Статус'),
            'deviceStatus' => Yii::t('app', 'Статус'),
            'nodeUuid' => Yii::t('app', 'Контроллер'),
            'node' => Yii::t('app', 'Контроллер'),
            'objectUuid' => Yii::t('app', 'Объект'),
            'object' => Yii::t('app', 'Объект'),
            'address' => Yii::t('app', 'URL'),
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
    public function getNode()
    {
        return $this->hasOne(Node::class, ['uuid' => 'nodeUuid']);
    }

    /**
     * @return ActiveQuery
     */
    public function getObject()
    {
        return $this->hasOne(Objects::class, ['uuid' => 'objectUuid']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPhoto() {
        return $this->hasMany(Photo::class, ['equipmentUuid' => 'uuid']);
    }

    /**
     * @return ActiveQuery
     */
    public function getOrganisation()
    {
        return $this->hasOne(Organisation::class, ['uuid' => 'oid']);
    }
}
