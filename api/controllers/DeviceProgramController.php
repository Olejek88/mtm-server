<?php

namespace api\controllers;

use common\components\MtmActiveRecord;
use common\models\DeviceProgram;
use common\models\Node;
use common\models\Organisation;
use common\models\User;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
//use yii\filters\auth\HttpBearerAuth;
use yii\web\BadRequestHttpException;
use yii\rest\Controller;
use yii\web\HttpException;
use yii\web\Response;

class DeviceProgramController extends Controller
{
    public $modelClass = DeviceProgram::class;

    /**
     * @inheritdoc
     */
    public function verbs()
    {
        $verbs = parent::verbs();
//        $verbs['create'] = ['POST'];
        $verbs['index'] = ['GET'];
        $verbs['send'] = ['POST'];
        return $verbs;
    }

    /**
     * @inheritdoc
     */
//    public function behaviors()
//    {
//        $behaviors = parent::behaviors();
//        $behaviors['authenticator']['class'] = HttpBearerAuth::class;
//        return $behaviors;
//    }

    /**
     * @return array|ActiveRecord[]
     * @throws HttpException
     */
    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request;

        // проверяем параметры запроса
        $oid = $req->getQueryParam('oid');
        if ($oid == null) {
            throw new HttpException(404, 'The specified post cannot be found.');
        } else {
            $organisation = Organisation::findOne($oid);
            if ($organisation == null) {
                throw new HttpException(404, 'The specified post cannot be found.');
            }
        }

        $user = new User();
        $user->oid = $organisation->uuid;
        Yii::$app->user->identity = $user;

        /** @var ActiveRecord $class */
        $class = $this->modelClass;
        $query = $class::find();

        // проверяем параметры запроса
        $nid = $req->getQueryParam('nid');
        if ($nid == null) {
            throw new HttpException(404, 'The specified post cannot be found.');
        } else {
            $node = Node::findOne($nid);
            if ($node == null) {
                throw new HttpException(404, 'The specified post cannot be found.');
            } else {
//                $query->joinWith('device');
//                $query->andWhere(['device.nodeUuid' => $node->uuid]);
            }
        }

        // проверяем параметры запроса
        $changedAfter = $req->getQueryParam('changedAfter');
        if ($changedAfter != null) {
            $query->andWhere(['>=', $class::tableName() . '.changedAt', $changedAfter]);
        }

        // проверяем что хоть какие-то условия были заданы
        if ($query->where == null) {
            return [];
        }

        $result = $query->all();
        return $result;
    }

    /**
     * @throws HttpException
     * @throws InvalidConfigException
     */
    public function actionSend()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $req = Yii::$app->request;

        // проверяем параметры запроса
        $oid = $req->getBodyParam('oid');
        if ($oid == null) {
            throw new HttpException(404, 'The specified post cannot be found.');
        } else {
            $organisation = Organisation::findOne($oid);
            if ($organisation == null) {
                throw new HttpException(404, 'The specified post cannot be found.');
            }
        }

        $user = new User();
        $user->oid = $organisation->uuid;
        Yii::$app->user->identity = $user;

        // проверяем параметры запроса
        $nid = $req->getBodyParam('nid');
        if ($nid == null) {
            throw new HttpException(404, 'The specified post cannot be found.');
        } else {
            $node = Node::findOne($nid);
            if ($node == null) {
                throw new HttpException(404, 'The specified post cannot be found.');
            }
        }

        $items = json_decode($req->getBodyParam('items'), true);
        foreach ($items as $item) {
            $model = DeviceProgram::find()->where(['uuid' => $item['uuid']])->one();
            if ($model == null) {
                $model = new DeviceProgram();
            }

            $model->scenario = MtmActiveRecord::SCENARIO_CUSTOM_UPDATE;
            $model->_id = $item['_id'];
            $model->uuid = $item['uuid'];
            $model->oid = $organisation->uuid;
            $model->title = $item['title'];
            $model->period_title1 = $item['period_title1'];
            $model->time1 = $item['time1'];
            $model->value1 = $item['value1'];
            $model->period_title2 = $item['period_title2'];
            $model->time2 = $item['time2'];
            $model->value2 = $item['value2'];
            $model->period_title3 = $item['period_title3'];
            $model->time3 = $item['time3'];
            $model->value3 = $item['value3'];
            $model->period_title4 = $item['period_title4'];
            $model->time4 = $item['time4'];
            $model->value4 = $item['value4'];
            $model->period_title5 = $item['period_title5'];
            $model->time5 = $item['time5'];
            $model->value5 = $item['value5'];
            $model->createdAt = $item['createdAt'];
            $model->changedAt = $item['changedAt'];

            if (!$model->save()) {
                throw new HttpException(401, 'device_program not saved.');
            }
        }

//        throw new HttpException(401, 'Oops...');
        return [];
    }

    /**
     * @return array|void
     * @throws BadRequestHttpException
     */
    public function actionCreate()
    {
        throw new BadRequestHttpException();
    }
}