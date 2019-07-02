<?php

namespace backend\controllers;

use backend\models\Role;
use backend\models\UserSearch;
use common\components\MainFunctions;
use common\models\Journal;
use Yii;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use Throwable;
use Exception;
use backend\models\User as NewUser;
use common\models\User;

/**
 * UsersController implements the CRUD actions for Users model.
 */
class UserController extends Controller
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
     * Lists all Users models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 15;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Users model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     * @throws Exception
     */
    public function actionCreate()
    {
        $model = new NewUser;
        if ($model->load(Yii::$app->request->post()) && $newUser = $model->save()) {
            $role = new Role();
            if ($role->load(Yii::$app->request->post())) {
                $am = Yii::$app->getAuthManager();
                $newRole = $am->getRole($role->role);
                $am->assign($newRole, $newUser->_id);
            }
            return $this->redirect('view?id=' . $newUser->_id);
        } else {
            $am = Yii::$app->getAuthManager();
            $roles = $am->getRoles();
            $roleList = ArrayHelper::map($roles, 'name', 'description');

            $role = new Role();
            // значение по умолчанию
            $role->role = User::ROLE_OPERATOR;

            return $this->render('create', [
                'model' => $model,
                'role' => $role,
                'roleList' => $roleList,
            ]);
        }
    }

    /**
     * @param $id
     * @return string
     */
    public function actionUpdate($id)
    {
//        $model = new User;
//        $model = $model::find()->where(['_id' => $id])->one();
//
//        // сохраняем старое значение image
//        $oldImage = $model->image;
//
//        if ($model->load(Yii::$app->request->post())) {
//            // получаем изображение для последующего сохранения
//            $file = UploadedFile::getInstance($model, 'image');
//            if ($file && $file->tempName) {
//                $fileName = self::_saveFile($model, $file);
//                if ($fileName) {
//                    $model->image = $fileName;
//                } else {
//                    $model->image = $oldImage;
//                    // уведомить пользователя, админа о невозможности сохранить файл
//                }
//            } else {
//                $model->image = $oldImage;
//            }
//
//            if ($model->save()) {
//                MainFunctions::register('Обновлен профиль пользователя ' . $model->name);
//                //return $this->redirect(['view', 'id' => $model->_id]);
//            } else {
//                return $this->render(
//                    'update',
//                    [
//                        'model' => $model,
//                    ]
//                );
//            }
//        }
//        return $this->render(
//            'update',
//            [
//                'model' => $model,
//            ]
//        );
        return $this->redirect('/user/index');
    }

    /**
     * Deletes an existing Users model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws Throwable
     */
    public function actionDelete($id)
    {
//        $model = new User;
//        $model = $model::find()->where(['_id' => $id])->one();
//        $model->delete();

        return $this->redirect(['index']);
    }

    /**
     * Build tree of equipment by user
     * @return mixed
     */
    public function actionTable()
    {
        if (isset($_POST['editableAttribute'])) {
            $model = User::find()->where(['_id' => $_POST['editableKey']])->one();
            if ($_POST['editableAttribute']=='type') {
                $model['type']=intval($_POST['Users'][$_POST['editableIndex']]['type']);
                if ($model['active']==true) $model['active']=1;
                else $model['active']=0;
                $model->save();
                return json_encode($model->errors);
            }
            if ($_POST['editableAttribute']=='active') {
                if ($_POST['Users'][$_POST['editableIndex']]['active']==true)
                    $model['active']=1;
                else $model['active']=0;
                $model->save();
                return json_encode("hui2");
            }
        }

        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = 15;
        return $this->render(
            'table',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id Id.
     *
     * @return ActiveQuery the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::find()->where(['_id' => $id, 'oid' => User::getOid(Yii::$app->user->identity)])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Формируем код записи о событии
     * @param $date
     * @param $type
     * @param $id
     * @param $title
     * @param $text
     *
     * @return string
     */
    public static function formEvent($date, $type, $id, $title, $text)
    {
        $event = '<li>';
        if ($type == 'measure')
            $event .= '<i class="fa fa-wrench bg-red"></i>';
        if ($type == 'journal')
            $event .= '<i class="fa fa-calendar bg-aqua"></i>';

        $event .= '<div class="timeline-item">';
        $event .= '<span class="time"><i class="fa fa-clock-o"></i> ' . date("M j, Y h:m", strtotime($date)) . '</span>';
        if ($type == 'measure')
            $event .= '<h3 class="timeline-header">' . Html::a('Оператор снял данные &nbsp;',
                    ['/measure/view', 'id' => Html::encode($id)]) . $title . '</h3>';
        if ($type == 'journal')
            $event .= '<h3 class="timeline-header"><a href="#">Добавлено событие журнала</a></h3>';

        $event .= '<div class="timeline-body">' . $text . '</div>';
        $event .= '</div></li>';
        return $event;
    }

    /**
     * Displays a single Users model.
     *
     * @param integer $id Id.
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $user = $this->findModel($id)->one();
        if ($user) {
            $events = [];
            $journals = Journal::find()
                ->where(['=', 'userUuid', $user->uuid])
                ->limit(5)
                ->all();
            foreach ($journals as $journal) {
                $text = $journal['description'];
                $events[] = ['date' => $journal['date'], 'event' => self::formEvent($journal['date'], 'journal', 0,
                    $journal['description'], $text)];
            }

            $sort_events = MainFunctions::array_msort($events, ['date' => SORT_DESC]);
            return $this->render(
                'view',
                [
                    'model' => $user,
                    'events' => $sort_events,
                ]
            );
        }
    }

}
