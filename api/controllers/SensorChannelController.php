<?php

namespace api\controllers;

use common\components\MtmActiveRecord;
use common\models\Node;
use common\models\Organisation;
use common\models\SensorChannel;
use common\models\User;
use Yii;
use yii\db\ActiveRecord;
//use yii\filters\auth\HttpBearerAuth;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\rest\Controller;
use yii\web\Response;
use yii\base\InvalidConfigException;

class SensorChannelController extends Controller
{
    public $modelClass = SensorChannel::class;

    /**
     * @inheritdoc
     */
    public function verbs()
    {
        $verbs = parent::verbs();
//        $verbs['create'] = ['POST'];
//        $verbs['index'] = ['GET'];
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
     */
    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [];
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

        $items = $req->getBodyParam('items');
        foreach ($items as $item) {
            $model = SensorChannel::find()->where(['uuid' => $item['uuid']])->one();
            if ($model == null) {
                $model = new SensorChannel();
                $model->_id = $items['_id'];
                $model->uuid = $item['uuid'];
                $model->oid = $organisation->uuid;
                $model->createdAt = $items['createdAt'];
            }

            $model->scenario = MtmActiveRecord::SCENARIO_CUSTOM_UPDATE;
            $model->title = $item['title'];
            $model->register = $item['register'];
            $model->deviceUuid = $item['deviceUuid'];
            $model->measureTypeUuid = $item['measureTypeUuid'];
            $model->changedAt = $items['changedAt'];

            if (!$model->save()) {
                throw new HttpException(401, 'sensor channel not saved.');
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