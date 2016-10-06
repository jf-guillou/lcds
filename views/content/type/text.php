<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\datetime\DateTimePicker;

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

        <?= $form->field($model, 'data')->textarea(['rows' => 6]) ?>

        <div class="row">
            <div class="col-lg-2">
                <?= $form->field($model, 'duration')->textInput() ?>
            </div>
            <div class="col-lg-5">
                <?= $form->field($model, 'start_ts')->widget(DateTimePicker::className(), [
                        'pluginOptions' => ['format' => 'yyyy-mm-dd hh:mm:ss'],
                    ]) ?>
            </div>

            <div class="col-lg-5">
                <?= $form->field($model, 'end_ts')->widget(DateTimePicker::className(), [
                        'pluginOptions' => ['format' => 'yyyy-mm-dd hh:mm:ss'],
                    ]) ?>
            </div>
        </div>

        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
<script type="text/javascript">
window.jqReady.push(function() {
    
});
</script>
