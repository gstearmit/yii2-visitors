<?php

namespace johnsnook\ipFilter\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use johnsnook\ipFilter\models\Visitor;
use johnsnook\parsel\ParselQuery;
use johnsnook\parsel\lib\SqlFormatter;

/**
 * VisitorSearch represents the model behind the search form of `johnsnook\ipFilter\models\Visitor`.
 */
class VisitorSearch extends Visitor {

    /**
     * @var string Virtual field to pass user query for yii2-parsel
     */
    public $userQuery;

    /**
     * @var string Any parser errors that may have occurred
     */
    public $queryError;

    /**
     * @var string The sql string generated by ParselQuery. For debugging purposes
     */
    public $sql;

    /**
     * @var array The fields to search with ParselQuery
     */
    private $fields = ['v.city', 'v.region', 'v.country', 'v.organization', 'vl.request', 'vl.referer'];

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            //[['id'], 'integer'],
            [['ip', 'userQuery'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios() {
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
    public function search($params) {
        $this->load($params);

        if (empty($this->userQuery)) {
            $query = Visitor::find();
        } else {
            $query = Visitor::find()
                    ->select(['v.ip'])
                    ->distinct()
                    ->addSelect(['city', 'region', 'country', 'visits', 'updated_at'])
                    ->from('visitor v')
                    ->leftJoin('visitor_log vl', 'v.ip = vl.ip');
            $query = ParselQuery::build($query, $this->userQuery, $this->fields);
            $this->sql = SqlFormatter::format($query->createCommand()->getRawSql());
            $this->queryError = ParselQuery::$lastError;
        }


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'updated_at' => SORT_DESC,
                ]
            ],
        ]);

        return $dataProvider;
    }

}
