<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Content Types');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-type-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Content Type'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'html:ntext',
            'css:ntext',
            'js:ntext',
            // 'self_update:boolean',
            // 'append_params',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
