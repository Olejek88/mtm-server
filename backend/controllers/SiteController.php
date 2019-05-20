<?php
namespace backend\controllers;

use backend\models\UserSearch;
use backend\models\UsersSearch;
use common\models\City;
use common\models\Node;
use common\models\Organisation;
use common\models\Device;
use common\models\DeviceType;
use common\models\Objects;
use common\models\LoginForm;
use common\models\Measure;
use common\models\SensorChannel;
use common\models\Street;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Html;
use yii\web\Controller;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * Behaviors
     *
     * @inheritdoc
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['signup', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'dashboard', 'test', 'timeline'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Actions
     *
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['error']);
        return $actions;
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        /**
         * Работа с картой
         * [$online, $offline, $gpsOn, $gps description]
         *
         * @var $gpsOn - Список геоданных по онлайн пользователям
         * @var $gps - Список геоданных по оффлайн пользователям
         */
        $lats = array();

        return $this->render(
            'index',
            [
                'lats' => $lats,
            ]
        );
    }

    /**
     * Dashboard
     *
     * @return string
     */
    public function actionDashboard()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 15;

        $accountUser = Yii::$app->user->identity;
        $currentUser = User::find()
            ->where(['_id' => $accountUser['id']])
            ->asArray()
            ->one();

        $cityCount = City::find()->count();
        $streetCount = Street::find()->count();
        $objectsCount = Objects::find()->count();
        $deviceCount = Device::find()->count();
        $contragentCount = Organisation::find()->count();
        $nodesCount = Node::find()->count();
        $sensorChannelsCount = SensorChannel::find()->count();
        $deviceTypeCount = DeviceType::find()->count();
        $usersCount = User::find()->count();

        $last_measures = Measure::find()
            ->where('createdAt > (NOW()-(4*24*3600000));')
            ->count();
        $complete = 0;

        $measures = Measure::find()
            ->orderBy('date')
            ->all();

        $equipments = Device::find()
            ->orderBy('_id DESC')
            ->limit(20)
            ->all();

        $users = User::find()
            ->all();

        /**
         * Работа с картой
         */
        $deviceData = array();
        $devices = Device::find()->select('*')->all();
        $deviceList[] = $devices;
        $deviceCount = count($devices);
        $cnt = 0;
        $equipmentsGroup = 'var equipments=L.layerGroup([';
        $equipmentsList = '';
        foreach ($devices as $device) {
            if ($device["object"]["latitude"] > 0) {
                $equipmentsList .= 'var equipment'
                    . $device["_id"]
                    . '= L.marker([' . $device["latitude"]
                    . ',' . $device["longitude"]
                    . '], {icon: equipmentIcon}).bindPopup("<b>'
                    . $device["title"] . '</b><br/>'
                    . $device["devieType"]["title"] . '").openPopup();';
                if ($cnt > 0) {
                    $equipmentsGroup .= ',';
                }

                $equipmentsGroup .= 'equipment' . $device["_id"];
                $cnt++;
            }
        }
        $equipmentsGroup .= ']);' . PHP_EOL;

        return $this->render(
            'dashboard',
            [
                'cityCount' => $cityCount,
                'streetCount' => $streetCount,
                'usersCount' => $usersCount,
                'objectCount' => $objectsCount,
                'nodesCount' => $nodesCount,
                'channelsCount' => $sensorChannelsCount,
                'deviceTypeCount' => $deviceTypeCount,
                'deviceCount' => $deviceCount,
                'measures' => $measures,
                'equipments' => $equipments,
                'users' => $users,
                'equipmentsGroup' => $equipmentsGroup,
                'last_measures' => $last_measures,
                'complete' => $complete,
                'contragentCount' => $contragentCount,
                'currentUser' => $currentUser,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider
            ]
        );
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Action error
     *
     * @return string
     */
    public function actionError()
    {
        if (\Yii::$app->getUser()->isGuest) {
            Yii::$app->getResponse()->redirect("/")->send();
        } else {
            $exception = Yii::$app->errorHandler->exception;
            if ($exception !== null) {
                $statusCode = $exception->statusCode;
                $name = $exception->getName();
                $message = $exception->getMessage();
                return $this->render(
                    'error',
                    [
                        'exception' => $exception,
                        'name' => $name . " " . $statusCode,
                        'message' => $message
                    ]
                );
            }
        }

        return '';
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Формируем код записи о событии
     * @param $date
     * @param $type
     * @param $id
     * @param $title
     * @param $text
     * @param $user
     *
     * @return string
     */
    public static function formEvent($date, $type, $id, $title, $text, $user)
    {
        $event = '<li>';
        if ($type == 'measure')
            $event .= '<i class="fa fa-wrench bg-red"></i>';
        if ($type == 'alarm')
            $event .= '<i class="fa fa-calendar bg-aqua"></i>';

        $event .= '<div class="timeline-item">';
        $event .= '<span class="time"><i class="fa fa-clock-o"></i> ' . date("M j, Y h:m", strtotime($date)) . '</span>';
        if ($type == 'measure')
            $event .= '<span class="timeline-header" style="vertical-align: middle">
                        <span class="btn btn-primary btn-xs">' . $user . '</span>&nbsp;' .
                Html::a('Снято показание &nbsp;',
                    ['/measure/view', 'id' => Html::encode($id)]) . $title . '</span>';

        if ($type == 'alarm')
            $event .= '&nbsp;<span class="btn btn-primary btn-xs">' . $user . '</span>&nbsp;
                    <span class="timeline-header" style="vertical-align: middle">' .
                Html::a('Зафиксировано событие &nbsp;',
                    ['/alarm/view', 'id' => Html::encode($id)]) . $title . '</span>';

        $event .= '<div class="timeline-body">' . $text . '</div>';
        $event .= '</div></li>';
        return $event;
    }
}
