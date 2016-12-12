<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Content types');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-type-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'name',
            'usable:boolean',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{toggle}',
                'header' => Yii::t('app', 'Enabled'),
                'buttons' => [
                    'toggle' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-'.($model->enabled ? 'pause' : 'play').'"></span>', $url);
                    },
                ],
            ],
        ],
    ]); ?>
</div>
