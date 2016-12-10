<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\Device */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Devices'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="device-view">

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
        <?= Html::a(Yii::t('app', $model->enabled ? 'Disable' : 'Enable'), ['toggle', 'id' => $model->id], ['class' => 'btn '.($model->enabled ? 'btn-danger' : 'btn-primary')]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'name',
            'description',
            'last_auth',
            'enabled:boolean',
        ],
    ]) ?>

    <p>
        <?= Html::a(Yii::t('app', 'Add Screen'), ['link', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'name',
            'description',
            'duration',

            [
                'class' => 'yii\grid\ActionColumn',
                'controller' => 'screen',
                'template' => '{view} {update} {unlink}',
                'buttons' => [
                    'unlink' => function ($url, $_model) use ($model) {
                        return Html::a('<span class="glyphicon glyphicon-remove"></span>', Url::to(['unlink', 'id' => $model->id, 'screenId' => $_model->id]));
                    },
                ],
            ],
        ],
    ]); ?>

</div>
