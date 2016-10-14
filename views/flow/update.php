<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Flow */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Flow',
]).$model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Flows'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="flow-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'flows' => $flows,
    ]) ?>

</div>
