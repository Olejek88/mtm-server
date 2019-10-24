<?php

namespace common\models;

use common\components\MtmActiveRecord;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "node_control".
 *
 * @property int $_id
 * @property string $uuid
 * @property string $oid
 * @property string $nodeUuid
 * @property string $date
 * @property int $type
 * @property string $createdAt
 * @property string $changedAt
 *
 * @property Node $node
 * @property Organisation $organisation
 */
class NodeControl extends MtmActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'node_control';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uuid', 'oid', 'nodeUuid'], 'required'],
            [['date', 'createdAt', 'changedAt'], 'safe'],
            [['type'], 'integer'],
            [['uuid', 'oid', 'nodeUuid'], 'string', 'max' => 45],
            [['uuid'], 'unique'],
            [['oid', 'nodeUuid', 'date', 'type'], 'unique', 'targetAttribute' => ['oid', 'nodeUuid', 'date', 'type']],
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
            '_id' => 'Id',
            'uuid' => 'Uuid',
            'oid' => 'Oid',
            'nodeUuid' => 'Node Uuid',
            'date' => 'Date',
            'type' => 'Type',
            'createdAt' => 'Created At',
            'changedAt' => 'Changed At',
        ];
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
