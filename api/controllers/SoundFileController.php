<?php

namespace api\controllers;

use common\models\Node;
use common\models\Organisation;
use common\models\SoundFile;
use common\models\User;
use Yii;
use yii\db\ActiveRecord;
//use yii\filters\auth\HttpBearerAuth;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\rest\Controller;
use yii\web\Response;

class SoundFileController extends Controller
{
    public $modelClass = SoundFile::class;

    /**
     * @inheritdoc
     */
    public function verbs()
    {
        $verbs = parent::verbs();
//        $verbs['create'] = ['POST'];
        $verbs['index'] = ['GET'];
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

        $query->andWhere(['oid' => $organisation->uuid]);

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

        /*
         * Есть проблема рекурсии при выборке связанных объектов.
         * Это происходит когда выбирается связанный объект, который ссылается на объект который его содержит.
         * Это происходит через объявление полей модели в fields().
         * Для того чтобы избежать этого, нужно прямо указывать поля которые мы желаем выбрать в виде объектов.
         * для одного уровня вложенности
         * $query->with(['fieldName'])->asArray()->all()
         * для произвольного
         * $query->with(['fieldName' => function($query){
         *     $query->with(['someField'])->asArray();
         * }])->asArray()->all()
         */

        $result = $query->all();
        return $result;
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