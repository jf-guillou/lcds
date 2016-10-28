<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = Yii::t('app', 'Set roles for {username}', [
    'username' => $model->username,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="user-set-roles">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="user-roles-form">

        <?php $form = ActiveForm::begin(); ?>

        <div class="row">
            <div class="col-lg-6">
                <?= $form->field($model, 'roleName')->dropDownList($roles) ?>
            </div>
            <div class="col-lg-6">
                <?= $form->field($model, 'flows')->listBox($flows, ['multiple' => 'true']) ?>
            </div>
        </div>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Affect'), ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
</div>

<script type="text/javascript">
window.jqReady.push(function() {
    var flowableRoles = <?= json_encode($flowableRoles) ?>;
    $('#user-rolename').change(function() {
        $('#user-flows').parent().toggle(flowableRoles.indexOf($(this).val()) != -1)
    }).trigger('change');
});
</script>
