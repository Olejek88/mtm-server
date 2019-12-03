<?php

namespace common\models;

use common\components\MtmActiveRecord;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "area".
 *
 * @property int $_id
 * @property string $uuid
 * @property string $oid
 * @property string $title
 * @property string $createdAt
 * @property string $changedAt
 *
 * @property Organisation $organisation
 */
class Area extends MtmActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'area';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uuid', 'oid', 'title'], 'required'],
            [['createdAt', 'changedAt'], 'safe'],
            [['uuid', 'oid'], 'string', 'max' => 45],
            [['title'], 'string', 'max' => 128],
            [['uuid'], 'unique'],
            [['oid'], 'exist', 'skipOnError' => true, 'targetClass' => Organisation::class, 'targetAttribute' => ['oid' => 'uuid']],
            [['oid'], 'checkOrganizationOwn'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            '_id' => '№',
            'uuid' => 'Uuid',
            'title' => 'Название',
            'createdAt' => 'Создана',
            'changedAt' => 'Изменена',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getOrganisation()
    {
        return $this->hasOne(Organisation::class, ['uuid' => 'oid']);
    }
}
