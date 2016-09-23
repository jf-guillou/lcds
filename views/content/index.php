<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Contents');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            //'id',
            'name',
            'description',
            [
                'attribute' => 'type.name',
                'label' => \Yii::t('app', 'Type'),
                'value' => function ($model, $key, $index, $column) {
                    return \Yii::t('app', $model->type->name);
                },
            ],
            //'data:ntext',
            // 'duration',
            // 'start_ts',
            // 'end_ts',
            // 'add_ts',
            // 'enabled:boolean',

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
