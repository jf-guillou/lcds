<?php

use kartik\datetime\DateTimePicker;

/* @var $this yii\web\View */
/* @var $model app\models\Content */
?>
<div class="row">
    <div class="col-lg-2">
        <?= $form->field($model, 'duration')->textInput(['id' => 'content-duration']) ?>
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
