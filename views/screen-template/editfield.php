<?php

use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

Modal::begin([
    'header' => '<h2>'.Yii::t('app', 'Edit field').'</h2>',
]);

$form = ActiveForm::begin([
        'id' => 'screen-template-field-form',
    ]);

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
    <div class="col-lg-12">
        <?= $form->field($field, 'contentTypes')->checkboxList($contentTypesArray, []) ?>
    </div>
</div>

<?= $form->field($field, 'css')->textarea(['rows' => 5]) ?>

<?= $form->field($field, 'js')->textarea(['rows' => 5]) ?>

<?= $form->field($field, 'append_params')->textInput(['maxlength' => true]) ?>

<div class="form-group">
    <?= Html::submitButton(Yii::t('app', 'Update'), ['class' => 'btn btn-primary']) ?>
    <?= Html::a(Yii::t('app', 'Delete'), ['delete-field', 'id' => $field->id], ['class' => 'btn btn-danger field-delete']) ?>
</div>

<script type="text/javascript">
    var selfContentIds = <?= json_encode($selfContentIds); ?>;
    var currentField = <?= $field->id ?>;
</script>

<?php
ActiveForm::end();
Modal::end();
