<?php

namespace backend\models;

use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\AreaNode;

/**
 * AreaNodeSearch represents the model behind the search form of `common\models\AreaNode`.
 */
class AreaNodeSearch extends AreaNode
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['_id'], 'integer'],
            [['uuid', 'oid', 'areaUuid', 'nodeUuid', 'createdAt', 'changedAt'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     * @throws InvalidConfigException
     */
    public function search($params)
    {
        $query = AreaNode::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            '_id' => $this->_id,
            'createdAt' => $this->createdAt,
            'changedAt' => $this->changedAt,
        ]);

        $query->andFilterWhere(['like', 'uuid', $this->uuid])
            ->andFilterWhere(['like', 'oid', $this->oid])
            ->andFilterWhere(['like', 'areaUuid', $this->areaUuid])
            ->andFilterWhere(['like', 'nodeUuid', $this->nodeUuid]);

        return $dataProvider;
    }
}
