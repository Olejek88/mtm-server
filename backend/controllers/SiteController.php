<?php
namespace backend\controllers;

use backend\models\UserSearch;
use backend\models\UsersSearch;
use common\models\City;
use common\models\Organisation;
use common\models\Device;
use common\models\DeviceType;
use common\models\Objects;
use common\models\LoginForm;
use common\models\Measure;
use common\models\Street;
use common\models\User;
use common\models\Users;
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
            ->where(['user_id' => $accountUser['id']])
            ->asArray()
            ->one();

        $cityCount = City::find()->count();
        $streetCount = Street::find()->count();
        $flatCount = Objects::find()->count();
        $equipmentCount = Device::find()->count();
        $contragentCount = Organisation::find()->count();
        $equipmentTypeCount = DeviceType::find()->count();
        $usersCount = User::find()->count();

        $last_measures = Measure::find()
            ->where('createdAt > (NOW()-(4*24*3600000));')
            ->count();
        $complete = 0;
        if ($flatCount > 0)
            $complete = number_format($last_measures * 100 / $flatCount, 2);

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
        $userData = array();
        $users = Users::find()->where('name != "sUser"')->select('*')->all();
        $userList[] = $users;
        $usersCount = count($users);

        $count = 0;
        $categories = "";
        $bar = "{ name: 'измерений',";
        $bar .= "data: [";
        foreach ($users as $current_user) {
            if ($count > 0) {
                $categories .= ',';
                $bar .= ",";
            }
            $categories .= '"' . $current_user['name'] . '"';
            $values[$count] = Measure::find()
                ->where('createdAt > (NOW()-(5*24*3600000))')
                ->andWhere(['userUuid' => $current_user['uuid']])
                ->count();
            $bar .= $values[$count];
            $count++;
        }
        $bar .= "]},";

        return $this->render(
            'dashboard',
            [
                'cityCount' => $cityCount,
                'streetCount' => $streetCount,
                'usersCount' => $usersCount,
                'flatCount' => $flatCount,
                'measures' => $measures,
                'equipments' => $equipments,
                'users' => $users,
                'categories' => $categories,
                'bar' => $bar,
                'last_measures' => $last_measures,
                'complete' => $complete,
                'equipmentCount' => $equipmentCount,
                'equipmentTypeCount' => $equipmentTypeCount,
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
     * @throws \yii\web\HttpException
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
