<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ScreenTemplate */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="screen-template-form">

    <p>
        <?= Html::a(Yii::t('app', 'Manage backgrounds'), ['template-background/index'], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Add a background'), ['template-background/create', 'template_id' => $model->id], ['class' => 'btn btn-success']) ?>
    </p>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?php
        $bgs = array_map(function ($bg) {
            return '<img src="'.$bg['uri'].'" alt="'.$bg['name'].'" class="img-preview"/><br />'.$bg['name'];
        }, $backgrounds);
    ?>
    <?= $form->field($model, 'background')->radioList($bgs, ['encode' => false]) ?>

    <?= $form->field($model, 'css')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
