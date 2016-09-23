<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\Flow */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Flows'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="flow-view">

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
        
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            //'id',
            'name',
            'description',
            'owner_group',
            'parent_id.name',
        ],
    ]) ?>

    <?= Html::a(Yii::t('app', 'Add content'), ['/content/generate', 'flowId' => $model->id], ['class' => 'btn btn-success']) ?>

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
                'controller' => 'content',
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
