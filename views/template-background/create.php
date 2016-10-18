<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\TemplateBackgroundUpload */

$this->title = Yii::t('app', 'Create Template Background');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Template Backgrounds'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="template-background-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="template-background-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'background')->fileInput() ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Create'), ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>
