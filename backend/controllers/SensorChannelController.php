<?php
namespace backend\controllers;

use backend\models\SensorChannelSearch;
use common\models\SensorChannel;
use common\models\User;
use Exception;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * StageOperationController implements the CRUD actions for StageOperation model.
 */
class SensorChannelController extends Controller
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

    // отключаем проверку для внешних запросов
    /**
     * @param $action
     * @return bool
     * @throws BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if ($action->id === 'move') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Lists all StageOperation models.
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    public function actionIndex()
    {
        $searchModel = new SensorChannelSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render(
            'index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Lists all models.
     *
     * @return mixed
     * @throws InvalidConfigException
     */
    public function actionTable()
    {
        $searchModel = new SensorChannelSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render(
            'table', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Displays a single StageOperation model.
     *
     * @param integer $id Id
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->render(
            'view',
            [
                'model' => $this->findModel($id),
            ]
        );
    }

    /**
     * Tree of stage and operation templates
     *
     * @return mixed
     */
    public function actionTree()
    {
        $indexTable = array();
        return $this->render(
            'tree', [
            ]
        );
    }

    /**
     * Creates a new StageOperation model.
     * If creation is successful, the browser will be redirected to
     * the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return $this->redirect('/site/index');
        }

        $model = new SensorChannel();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->_id]);
        } else {
            return $this->render(
                'create',
                [
                    'model' => $model,
                ]
            );
        }
    }

    /**
     * Updates an existing StageOperation model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id Id
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return $this->redirect('/site/index');
        }

        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->_id]);
        } else {
            return $this->render(
                'update',
                [
                    'model' => $model,
                ]
            );
        }
    }

    /**
     * Deletes an existing StageOperation model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param integer $id Id
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws Exception
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function actionDelete($id)
    {
        if (!Yii::$app->user->can(User::PERMISSION_ADMIN)) {
            return $this->redirect('/site/index');
        }

        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the StageOperation model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id Id
     *
     * @return SensorChannel the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SensorChannel::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
