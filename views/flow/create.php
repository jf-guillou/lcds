<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Flow */

$this->title = Yii::t('app', 'Create Flow');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Flows'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="flow-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
