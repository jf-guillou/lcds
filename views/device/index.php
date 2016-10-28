<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Devices');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="device-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Device'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'name',
            'description',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete} {toggle}',
                'buttons' => [
                    'toggle' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-'.($model->enabled ? 'pause' : 'play').'"></span>', $url);
                    },
                ],
            ],
        ],
    ]); ?>
</div>
