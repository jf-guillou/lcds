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
            <div class="col-lg-12">
                <?= $form->field($model, 'data')->textInput(['maxlength' => true, 'id' => 'content-data', 'placeholder' => '12.150,-6.320'])->label(Yii::t('app', 'Latitude & longitude')) ?>
            </div>
        </div>

        <?= $this->render('_time', [
            'model' => $model,
            'form' => $form,
        ]) ?>

        <?php ActiveForm::end(); ?>

    </div>

</div>
<script type="text/javascript">
window.jqReady.push(function() {
});
</script>
