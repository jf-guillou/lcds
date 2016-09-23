<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Screen */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Screen',
]).$model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Screens'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="screen-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'templates' => $templates,
    ]) ?>

</div>
