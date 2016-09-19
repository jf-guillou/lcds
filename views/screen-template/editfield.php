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
                    window.location.reload();
                } else {
                    $("#field-modal").html($(resp));
                    $('.modal').modal('show');
                }
            }
        })
        return false;
    });
    $(document).on('click', '.field-delete', function() {
        if (!confirm('<?= Yii::t('app', 'Are you sure you want to delete this item?') ?>')) {
            return false;
        }
        var $btn = $(this);
        console.log($btn);
        $.ajax({
            url: $btn.attr('href'),
            type: 'GET',
            success: function(resp) {
                $('.modal').modal('hide');
                if (resp.success) {
                    removeField(<?= $field->id ?>);
                } else {
                    alert(resp.message);
                }
            }
        });
        return false;
    });

    var selfContentIds = <?= json_encode($selfContentIds); ?>;
    $(document).on('change', '#field-contenttypes input', function() {
        var $chk = $(this);
        if (selfContentIds.indexOf($chk.val()*1) != -1) {
            $('#field-contenttypes input').not($chk).prop('checked', false);
        } else {
            $('#field-contenttypes input').filter(function() { return selfContentIds.indexOf($(this).val()*1) != -1 }).prop('checked', false);
        }
    });
</script>

<?php
ActiveForm::end();
Modal::end();
