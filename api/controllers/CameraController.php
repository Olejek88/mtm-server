<?php

namespace api\controllers;

use common\components\MtmActiveRecord;
use common\models\Camera;
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

class CameraController extends Controller
{
    public $modelClass = Camera::class;

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

        $items = $req->getBodyParam('items');
        foreach ($items as $item) {
            $model = Camera::find()->where(['uuid' => $item['uuid']])->one();
            if ($model == null) {
                throw new HttpException(404, 'The specified post cannot be found.');
            }

            $model->scenario = MtmActiveRecord::SCENARIO_CUSTOM_UPDATE;
//            $model->uuid = $item['uuid'];
//            $model->oid = $organisation->uuid;
            $model->title = $item['title'];
            $model->deviceStatusUuid = $item['deviceStatusUuid'];
            $model->address = $item['address'];
            $model->changedAt = $item['changedAt'];

            if (!$model->save()) {
                throw new HttpException(401, 'camera not saved.');
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