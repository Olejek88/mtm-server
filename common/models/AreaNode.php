<?php

namespace common\models;

use common\components\MtmActiveRecord;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "area_node".
 *
 * @property int $_id
 * @property string $uuid
 * @property string $oid
 * @property string $areaUuid
 * @property string $nodeUuid
 * @property string $createdAt
 * @property string $changedAt
 *
 * @property Area $area
 * @property Node $node
 * @property Organisation $organisation
 */
class AreaNode extends MtmActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'area_node';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uuid', 'oid', 'areaUuid', 'nodeUuid'], 'required'],
            [['createdAt', 'changedAt'], 'safe'],
            [['uuid', 'oid', 'areaUuid', 'nodeUuid'], 'string', 'max' => 45],
            [['uuid'], 'unique'],
            [['areaUuid', 'nodeUuid'], 'unique', 'targetAttribute' => ['areaUuid', 'nodeUuid']],
            [['nodeUuid'], 'exist', 'skipOnError' => true, 'targetClass' => Node::class, 'targetAttribute' => ['nodeUuid' => 'uuid']],
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
            'areaUuid' => 'Uuid территории',
            'nodeUuid' => 'Uuid шкафа',
            'createdAt' => 'Создана',
            'changedAt' => 'Именена',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getArea()
    {
        return $this->hasOne(Area::class, ['uuid' => 'areaUuid']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNode()
    {
        return $this->hasOne(Node::class, ['uuid' => 'nodeUuid']);
    }

    /**
     * @return ActiveQuery
     */
    public function getOrganisation()
    {
        return $this->hasOne(Organisation::class, ['uuid' => 'oid']);
    }
}
