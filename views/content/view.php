<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Content */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contents'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-view">

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
            'id',
            'name',
            'description',
            'type.tName',
            'data:ntext',
            'duration',
            'start_ts',
            'end_ts',
            'add_ts',
            'enabled:boolean',
        ],
    ]) ?>

    <?php if (is_subclass_of($model, 'app\models\types\Media')) : ?>
    <div class="text-center">
        <h3><?= Yii::t('app', 'Preview'); ?></h3>
        <div class="media-preview">
            <?= $model->getData() ?>
        </div>
    </div>
    <?php endif; ?>
</div>
