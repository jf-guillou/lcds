<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $types[] app\models\ContentTypes */

$this->title = Yii::t('app', 'Content type choice');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contents'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-type-choice">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <?php
        foreach ($types as $i => $t) : ?>

        <div class="col-lg-3">
            <h3><?= $t->name ?></h3>
            <p>
                <?php
                if ($t->exemple) : ?>
                <img src="<?= Url::to($t->exemple) ?>" alt="<?= $t->name ?>" class="img-full" />
                <?php
                endif; ?>
            </p>
            <p>
                <?= $t->description ?>
            </p>
            <p class="text-center">
                <?= Html::a(Yii::t('app', 'Use'), ['', 'flowId' => $flow, 'type' => $t->id], ['class' => 'btn btn-success']) ?>
            </p>
        </div>

        <?php
        endforeach; ?>
    </div>
</div>
