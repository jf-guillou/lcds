<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Flows');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="flow-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Flow'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],

            //'id',
            'name',
            'description',
            'owner_group',
            'parent.name',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
