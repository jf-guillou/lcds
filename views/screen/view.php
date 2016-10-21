<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\Screen */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Screens'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="screen-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a(Yii::t('app', 'Preview'), ['/frontend/screen', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'name',
            'description',
            [
                'label' => \Yii::t('app', 'Template'),
                'value' => $model->template ? $model->template->name : null,
            ],
        ],
    ]) ?>

    <p>
        <?= Html::a(Yii::t('app', 'Add flow'), ['link', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'name',
            'description',

            [
                'class' => 'yii\grid\ActionColumn',
                'controller' => 'flow',
                'template' => '{view} {update} {unlink}',
                'buttons' => [
                    'unlink' => function ($url, $_model, $key) use ($model) {
                        return Html::a('<span class="glyphicon glyphicon-remove"></span>', Url::to(['unlink', 'id' => $model->id, 'flowId' => $_model->id]));
                    },
                ],
            ],
        ],
    ]); ?>

</div>
