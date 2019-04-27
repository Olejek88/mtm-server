<?php

namespace api\controllers;

use api\components\BaseController;
use common\models\DeviceStatus;
use yii\db\ActiveRecord;

class EquipmentStatusController extends BaseController
{
    /** @var ActiveRecord $modelClass */
    public $modelClass = DeviceStatus::class;
}
