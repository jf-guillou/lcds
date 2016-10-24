<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Screens');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="screen-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Screen'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            //'id',
            'name',
            'description',
            //'template_id',
            [
                'attribute' => 'template',
                'label' => Yii::t('app', 'Template'),
                'value' => 'template.name',
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete} {toggle}',
                'buttons' => [
                    'toggle' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-'.($model->active ? 'pause' : 'play').'"></span>', $url);
                    },
                ],
            ],
        ],
    ]); ?>
</div>
