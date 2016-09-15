<?php

use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

Modal::begin([
    'header' => '<h2>'.Yii::t('app', 'Edit field').'</h2>',
]);

$form = ActiveForm::begin();

//return var_dump($contentTypes);
?>

<div class="row">
    <div class="col-lg-6">
        <?= $form->field($field, 'id')->textInput(['maxlength' => true, 'readonly' => true]); ?>
    </div>
    <div class="col-lg-6">
        <?= $form->field($field, 'template_id')->textInput(['maxlength' => true, 'readonly' => true]); ?>
    </div>
</div>

<div class="row">
    <div class="col-lg-3">
        <?= $form->field($field, 'x1')->textInput(['maxlength' => true, 'readonly' => true]); ?>
    </div>
    <div class="col-lg-3">
        <?= $form->field($field, 'x2')->textInput(['maxlength' => true, 'readonly' => true]); ?>
    </div>
    <div class="col-lg-3">
        <?= $form->field($field, 'y1')->textInput(['maxlength' => true, 'readonly' => true]); ?>
    </div>
    <div class="col-lg-3">
        <?= $form->field($field, 'y2')->textInput(['maxlength' => true, 'readonly' => true]); ?>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <?= $form->field($field, 'contentTypes')->checkboxList($selfContentTypes, []) ?>
    </div>

    <div class="col-lg-6">
        <?= $form->field($field, 'contentTypes')->checkboxList($contentTypes, []) ?>
    </div>
</div>

<?= $form->field($field, 'css')->textarea(['rows' => 5]) ?>

<?= $form->field($field, 'js')->textarea(['rows' => 5]) ?>

<?= $form->field($field, 'append_params')->textInput(['maxlength' => true]) ?>

<?= Html::submitButton(Yii::t('app', 'Update'), ['class' => 'btn btn-primary']) ?>

<script type="text/javascript">
    var cTypes = $('.field-field-contenttypes');
</script>

<?php
ActiveForm::end();
Modal::end();
