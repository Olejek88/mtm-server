<?php

namespace backend\controllers;

use backend\models\CameraSearch;
use common\models\Camera;
use common\models\DeviceStatus;
use common\models\House;
use common\models\Node;
use common\models\Objects;
use common\models\Street;
use Yii;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\base\InvalidConfigException;
use Throwable;

class CameraController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Camera models.
     * @return mixed
     */
    public function actionIndex()
    {
        if (isset($_POST['editableAttribute'])) {
            $model = Camera::find()
                ->where(['_id' => $_POST['editableKey']])
                ->one();
            if ($_POST['editableAttribute'] == 'port') {
                $model['port'] = $_POST['Camera'][$_POST['editableIndex']]['port'];
            }
            if ($_POST['editableAttribute'] == 'deviceStatusUuid') {
                $model['deviceStatusUuid'] = $_POST['Camera'][$_POST['editableIndex']]['deviceStatusUuid'];
            }
            if ($_POST['editableAttribute'] == 'date') {
                $model['date'] = date("Y-m-d H:i:s", $_POST['Camera'][$_POST['editableIndex']]['date']);
            }
            $model->save();
            return json_encode('');
        }

        $searchModel = new CameraSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 15;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Camera model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Camera model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Camera();
        $searchModel = new CameraSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 15;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->_id]);
        } else {
            return $this->render('create', [
                'model' => $model, 'dataProvider' => $dataProvider
            ]);
        }
    }

    /**
     * Updates an existing Camera model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Camera model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Camera model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Camera the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Camera::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Build tree of device
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    public function actionTree()
    {
        ini_set('memory_limit', '-1');
        $fullTree = array();
        $streets = Street::find()
            ->select('*')
            ->orderBy('title')
            ->all();
        foreach ($streets as $street) {
            $fullTree['children'][] = [
                'title' => $street['title'],
                'folder' => true
            ];
            $houses = House::find()->where(['streetUuid' => $street['uuid']])->
            orderBy('number')->all();
            foreach ($houses as $house) {
                $childIdx = count($fullTree['children']) - 1;
                $fullTree['children'][$childIdx]['children'][] = [
                    'title' => $house->getFullTitle(),
                    'folder' => true
                ];
                $objects = Objects::find()->where(['houseUuid' => $house['uuid']])->all();
                foreach ($objects as $object) {
                    $childIdx2 = count($fullTree['children'][$childIdx]['children']) - 1;
                    $fullTree['children'][$childIdx]['children'][$childIdx2]['children'][] = [
                        'title' => $object['objectType']['title'] . ' ' . $object['title'],
                        'folder' => true
                    ];
                    $nodes = Node::find()->where(['objectUuid' => $object['uuid']])->all();
                    foreach ($nodes as $node) {
                        $childIdx3 = count($fullTree['children'][$childIdx]['children'][$childIdx2]['children']) - 1;
                        if ($node['deviceStatusUuid'] == DeviceStatus::NOT_MOUNTED) {
                            $class = 'critical1';
                        } elseif ($node['deviceStatusUuid'] == DeviceStatus::NOT_WORK) {
                            $class = 'critical2';
                        } else {
                            $class = 'critical3';
                        }
                        $fullTree['children'][$childIdx]['children'][$childIdx2]['children'][$childIdx3]['children'][] = [
                            'status' => '<div class="progress"><div class="' . $class . '">' . $node['deviceStatus']->title . '</div></div>',
                            'title' => 'Контроллер [' . $node['address'] . ']',
                            'register' => $node['address'],
                            'folder' => true
                        ];
                        $cameras = Camera::find()->where(['nodeUuid' => $node['uuid']])->all();
                        foreach ($cameras as $camera) {
                            $childIdx4 = count($fullTree['children'][$childIdx]['children'][$childIdx2]['children'][$childIdx3]['children']) - 1;
                            if ($camera['deviceStatusUuid'] == DeviceStatus::NOT_MOUNTED) {
                                $class = 'critical1';
                            } elseif ($camera['deviceStatusUuid'] == DeviceStatus::NOT_WORK) {
                                $class = 'critical2';
                            } else {
                                $class = 'critical3';
                            }
                            $fullTree['children'][$childIdx]['children'][$childIdx2]['children'][$childIdx3]['children'][$childIdx4]['children'][] = [
                                'title' => $camera['title'],
                                'status' => '<div class="progress"><div class="'
                                    . $class . '">' . $camera['deviceStatus']->title . '</div></div>',
                                'register' => $camera['port'].' ['.$camera['address'].':'.$camera['port'].']',
                                'date' => $camera['changedAt'],
                                'folder' => false
                            ];
                        }
                    }
                }
            }
        }
        return $this->render(
            'tree',
            ['device' => $fullTree]
        );
    }
}
