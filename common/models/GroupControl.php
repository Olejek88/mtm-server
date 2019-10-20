<?php

namespace common\models;

use common\components\MtmActiveRecord;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * This is the model class for table "group_control".
 *
 * @property integer $_id
 * @property string $uuid
 * @property string $oid идентификатор организации
 * @property integer $type
 * @property string $date
 * @property string $groupUuid
 * @property string $deviceProgramUuid
 * @property string $createdAt
 * @property string $changedAt
 *
 * @property ActiveQuery $deviceProgram
 * @property Group $group
 */
class GroupControl extends MtmActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'group_control';
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
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uuid', 'type', 'date', 'groupUuid'], 'required'],
            [['oid', 'createdAt', 'changedAt'], 'safe'],
            [['changedAt'], 'string', 'on' => self::SCENARIO_CUSTOM_UPDATE],
            [['uuid', 'date', 'groupUuid'], 'string', 'max' => 50],
            [['oid'], 'checkOrganizationOwn'],
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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => Yii::t('app', '№'),
            'uuid' => Yii::t('app', 'Uuid'),
            'type' => Yii::t('app', 'Тип'),
            'date' => Yii::t('app', 'Дата'),
            'group' => Yii::t('app', 'Группа'),
            'groupUuid' => Yii::t('app', 'Группа'),
            'createdAt' => Yii::t('app', 'Создан'),
            'changedAt' => Yii::t('app', 'Изменен'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::class, ['uuid' => 'groupUuid']);
    }

    /**
     * @return ActiveQuery
     */
    public function getDeviceProgram()
    {
        return $this->hasOne(DeviceProgram::class, ['uuid' => 'deviceProgramUuid']);
    }
}
