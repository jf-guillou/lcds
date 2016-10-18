<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ScreenTemplate */

$this->title = Yii::t('app', 'Create Screen Template');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Screen Templates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="screen-template-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'backgrounds' => $backgrounds,
    ]) ?>

</div>
