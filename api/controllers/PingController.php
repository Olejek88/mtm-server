<?php

namespace api\controllers;

use api\components\BaseController;
use common\models\Alarm;

class PingController extends BaseController
{
    public $modelClass = null;

    public function actionIndex() {
        return 'PONG';
    }
}
