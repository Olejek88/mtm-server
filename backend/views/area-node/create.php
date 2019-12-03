<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\AreaNode */

$this->title = 'Добавить шкаф к территории';
$this->params['breadcrumbs'][] = ['label' => 'Шкафы по территориям', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="area-node-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
