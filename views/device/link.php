<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\Device */

$this->title = Yii::t('app', 'Add Screen');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Devices'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="device-link">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'name',
            'description',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{link}',
                'header' => Yii::t('app', 'Link'),
                'buttons' => [
                    'link' => function ($url, $_model) use ($model) {
                        return Html::a('<span class="glyphicon glyphicon-plus"></span>', Url::to(['link', 'id' => $model->id, 'screenId' => $_model->id]));
                    },
                ],
            ],
        ],
    ]); ?>

</div>
