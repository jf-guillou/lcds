<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $types[] app\models\ContentTypes */

$this->title = Yii::t('app', 'Content type choice');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contents'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-type-choice">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'name',
                'value' => function ($model, $key, $index, $column) {
                    return Yii::t('app', $model->name);
                },
            ],
            [
                'attribute' => 'description',
                'value' => function ($model, $key, $index, $column) {
                    return Yii::t('app', $model->description);
                },
            ],
            [
                'label' => Yii::t('app', 'Action'),
                'format' => 'html',
                'value' => function ($model, $key, $index, $column) use ($flow) {
                    return Html::a(Yii::t('app', 'Use'), ['', 'flowId' => $flow, 'type' => $model->id], ['class' => 'btn btn-success']);
                },
            ],

        ],
    ]); ?>

</div>
