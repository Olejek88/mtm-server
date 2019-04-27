<?php

namespace api\controllers;

use api\components\BaseController;
use common\models\DeviceType;
use yii\db\ActiveRecord;

class EquipmentTypeController extends BaseController
{
    /** @var ActiveRecord $modelClass */
    public $modelClass = DeviceType::class;
}
