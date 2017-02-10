<?php

use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ScreenTemplate */
/* @var $form yii\widgets\ActiveForm */

Modal::begin([
    'id' => 'template-background-modal',
    'header' => '<h2>'.Yii::t('app', 'Template Backgrounds').'</h2>',
]);
Modal::end();
?>

<div class="screen-template-form">

    <p>
        <?= Html::a(Yii::t('app', 'Manage backgrounds'), ['template-background/index'], ['class' => 'btn btn-primary aj-modal']) ?>
        <?= Html::a(Yii::t('app', 'Add a background'), ['template-background/create'], ['class' => 'btn btn-success aj-modal']) ?>
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
<script type="text/javascript">
window.jqReady.push(function() {
    $('.aj-modal').click(function() {
        $('#template-background-modal').modal('show').attr('href', $(this).attr('href')).find('.modal-body').load($(this).attr('href'));
        return false;
    });

    $('#template-background-modal').on('click', 'a', function() {
        $.ajax({
            url: $(this).attr('href'),
            method: $(this).attr('data-method') || 'GET',
            success: function(r) {
                if (!r) {
                    window.location.reload();
                    return;
                }
                $('#template-background-modal').modal('show').find('.modal-body').load($('#template-background-modal').attr('href'));
            }
        });
        
        return false;
    });

    $('#template-background-modal').on('submit', 'form', function() {
        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: new FormData(this),
            contentType: false,
            processData: false,
            success: function(r) {
                if (!r) {
                    window.location.reload();
                    return;
                }
                $('#template-background-modal').modal('show').find('.modal-body').html(r);
            }
        });
        return false;
    });
});
</script>
