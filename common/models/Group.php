<?php

namespace common\models;

use common\components\MtmActiveRecord;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * This is the model class for table "group".
 *
 * @property integer $_id
 * @property string $uuid
 * @property string $oid идентификатор организации
 * @property string $title
 * @property int $groupId
 * @property string $deviceProgramUuid
 * @property string $createdAt
 * @property string $changedAt
 *
 * @property Organisation $organisation
 * @property DeviceProgram $deviceProgram
 */
class Group extends MtmActiveRecord
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
        return 'group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uuid', 'title'], 'required'],
            [['createdAt', 'changedAt'], 'safe'],
            [['uuid', 'title', 'oid'], 'string', 'max' => 50],
            [['groupId'], 'integer', 'min' => 0, 'max' => 15],
            [
                ['deviceProgramUuid'],
                'exist', 'skipOnError' => true,
                'targetClass' => DeviceProgram::class,
                'targetAttribute' => ['deviceProgramUuid' => 'uuid']
            ],
            [['oid'], 'checkOrganizationOwn'],
        ];
    }

    public function fields1()
    {
        return [
            '_id',
            'uuid',
            'title',
            'createdAt',
            'changedAt',
        ];
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
            'createdAt' => Yii::t('app', 'Создан'),
            'changedAt' => Yii::t('app', 'Изменен'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getDeviceProgram()
    {
        return $this->hasOne(DeviceProgram::class, ['uuid' => 'deviceProgramUuid']);
    }

    /**
     * @return ActiveQuery
     */
    public function getOrganisation()
    {
        return $this->hasOne(Organisation::class, ['uuid' => 'oid']);
    }
}
