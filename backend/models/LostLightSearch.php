<?php

namespace backend\models;

use common\models\LostLight;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * NodeSearch represents the model behind the search form about `common\models\Node`.
 */
class LostLightSearch extends LostLight
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['_id'], 'integer'],
            [['uuid', 'deviceTypeUuid', 'nodeUuid', 'date', 'deviceStatusUuid', 'serial', 'port', 'object',
                'latitude', 'longitude', 'createdAt', 'changedAt'], 'safe'],
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
     * @throws InvalidConfigException
     */
    public function search($params): ActiveDataProvider
    {
        $query = LostLight::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'date' => $this->date,
            'nodeUuid' => $this->nodeUuid,
        ]);

        $query->orderBy(['date' => SORT_ASC]);

        return $dataProvider;
    }
}
