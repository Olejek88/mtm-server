<?php

namespace backend\models;

use common\models\ObjectStatus;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ObjectSearchStatus represents the model behind the search form about `common\models\ObjectStatus`.
 */
class ObjectSearchStatus extends ObjectStatus
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['_id'], 'integer'],
            [['uuid', 'title', 'createdAt', 'changedAt'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
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
     */
    public function search($params)
    {
        $query = ObjectStatus::find();

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
            ->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }
}
