<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Content */

$this->title = Yii::t('app', 'Create {type} content', ['type' => Yii::t('app', $type->name)]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contents'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-type-text">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="content-form">

        <?php $form = ActiveForm::begin(); ?>

        <div class="row">
            <div class="col-lg-4">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-lg-8">
                <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <?= $form->field($model, 'data')->textInput(['maxlength' => true, 'id' => 'content-data'])->label(Yii::t('app', 'URL')) ?>
            </div>
            <div class="col-lg-4">
                <div id="content-preview">
                </div>
            </div>
        </div>

        <?= $this->render('_time', [
            'model' => $model,
            'form' => $form,
        ]) ?>

        <?php ActiveForm::end(); ?>

    </div>

</div>
<style type="text/css">
#content-preview {
    max-width: 100%;
}
#content-preview > * {
    max-width: 100%;
}
</style>
<script type="text/javascript">
window.jqReady.push(function() {
    if (<?= $type->canPreview ? 'true' : 'false' ?>) {
        var $preview = $('#content-preview');
        var html = '<?= $type->html ?>';
        $('#content-data').change(function() {
            var c = $(this).val();
            if (c != '') {
                $preview.html(html.replace('%data%', c)).show();
            } else {
                $preview.hide().html('');
            }
        });
    }
});
</script>
