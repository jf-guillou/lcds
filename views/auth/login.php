<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Content */

$this->title = Yii::t('app', 'Login');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-login">

    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

    <div class="row">
        <div class="col-sm-6 col-md-4 col-md-offset-4">
            <h1><?= Html::encode($this->title) ?></h1>
            <?= $form->field($model, 'username')->textInput() ?>
            <?= $form->field($model, 'password')->passwordInput() ?>
            <?= $form->field($model, 'remember_me')->checkbox() ?>
            <?= Html::submitButton(Yii::t('app', 'Sign in'), ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
