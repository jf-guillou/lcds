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

<div class="form-group">
    <?= Html::submitButton(Yii::t('app', 'Update'), ['class' => 'btn btn-primary']) ?>
</div>

<script type="text/javascript">
    var cTypes = $('.field-field-contenttypes');
    $(document).on('beforeSubmit', '#screen-template-field-form', function() {
        var $form = $(this);
        if ($form.find('.has-error').length) {
            return false;
        }

        $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: $form.serialize(),
            success: function(resp) {
                if (resp == '') {
                    $('.modal').modal('hide');
                } else {
                    $("#field-modal").html($(resp));
                    $('.modal').modal('show');
                }
            }
        })
        return false;
    });
</script>

<?php
ActiveForm::end();
Modal::end();
