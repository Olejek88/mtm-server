<?php

namespace api\controllers;

use common\components\MtmActiveRecord;
use common\models\Device;
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

class DeviceController extends Controller
{
    public $modelClass = Device::class;

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
                $query->andWhere(['nodeUuid' => $node->uuid]);
            }
        }

        // проверяем параметры запроса
        $changedAfter = $req->getQueryParam('changedAfter');
        if ($changedAfter != null) {
            $query->andWhere(['>=', 'changedAt', $changedAfter]);
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
            $model = Device::find()->where(['uuid' => $item['uuid']])->one();
            if ($model == null) {
                $model = new Device();
                $model->uuid = $item['uuid'];
                $model->oid = $organisation->uuid;
            }

            $model->scenario = MtmActiveRecord::SCENARIO_CUSTOM_UPDATE;
            $model->address = $item['address'];
            $model->name = $item['name'];
            $model->serial = $item['serial'];
            $model->port = $item['port'];
            $model->interface = $item['interface'];
            $model->deviceStatusUuid = $item['deviceStatusUuid'];
            $model->date = $item['last_date'];
            $model->createdAt = $item['createdAt'];
            $model->changedAt = $item['changedAt'];

            if (!$model->save()) {
                throw new HttpException(401, 'device not saved.');
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