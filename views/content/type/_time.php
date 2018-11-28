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
        <?= $form->field($model, 'start_ts')->widget(DateTimePicker::class, [
            'pluginOptions' => [
                'minView' => 'day',
                'startDate' => '-0d',
                'format' => 'yyyy-mm-dd hh:ii:ss',
                'todayBtn' => true,
            ],
        ]) ?>
    </div>

    <div class="col-lg-5">
        <?= $form->field($model, 'end_ts')->widget(DateTimePicker::class, [
            'pluginOptions' => [
                'minView' => 'day',
                'startDate' => '-0d',
                'format' => 'yyyy-mm-dd hh:ii:ss',
                'todayBtn' => true,
            ],
        ]) ?>
    </div>
</div>
