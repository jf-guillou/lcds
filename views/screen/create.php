<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Screen */

$this->title = Yii::t('app', 'Create Screen');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Screens'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="screen-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Screen Template'), ['screen-template/create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= $this->render('_form', [
        'model' => $model,
        'templates' => $templates,
    ]) ?>

</div>
