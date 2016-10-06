<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Set Roles'), ['set-roles', 'id' => $model->username], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->username], [
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
            'username',
            'last_login_at',
            [
                'attribute' => 'role',
                'value' => Yii::t('app', $model->role),
            ],
            [
                'attribute' => 'flows',
                'value' => count($model->flows) ? implode(', ', array_map(function ($f) {
                    return Yii::t('app', $f->name);
                }, $model->flows)) : null,
            ],
        ],
    ]) ?>

</div>
