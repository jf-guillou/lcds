<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Users');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create User'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Import User'), ['import'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'username',
            'last_login_at',
            [
                'attribute' => 'role',
                'value' => function ($model, $key, $index, $column) {
                    return Yii::t('app', $model->role);
                },
            ],

            ['class' => 'yii\grid\ActionColumn',
                'template' => '{view} {set-roles} {delete}',
                'buttons' => [
                    'set-roles' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-cog"></span>', $url);
                    },
                ],
            ],
        ],
    ]); ?>
</div>
