<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\Screen */

$this->title = Yii::t('app', 'Add flow');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Screens'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="screen-link">

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
                    'link' => function ($url, $_model, $key) use ($model) {
                        return Html::a('<span class="glyphicon glyphicon-plus"></span>', Url::to(['link', 'id' => $model->id, 'flowId' => $_model->id]));
                    },
                ],
            ],
        ],
    ]); ?>

</div>
