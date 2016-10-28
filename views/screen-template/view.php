<?php

use yii\bootstrap\Modal;
use yii\helpers\Html;
use app\assets\DesignerAsset;

/* @var $this yii\web\View */
/* @var $model app\models\ScreenTemplate */

DesignerAsset::register($this);

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Screen Templates'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="screen-template-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a(Yii::t('app', 'Add field'), ['add-field'], ['class' => 'btn btn-success field-add']) ?>
    </p>

    <div class="design-wrapper">
        <img src="<?= $background; ?>" alt="Background" class="img-full background-edit" />
        <div id="design" class="design"></div>
    </div>

</div>
<div id="field-modal">
<?php
Modal::begin([
    'header' => 'HEADER',
]);
Modal::end();
?>
</div>

<script type="text/javascript">
var templateId = <?= $model->id ?>;
var fields = <?= json_encode($fields) ?>;
var contentTypes = <?= json_encode($contentTypes) ?>;
var setFieldPosUrl = '<?= $setFieldPosUrl ?>';
var editFieldUrl = '<?= $editFieldUrl ?>';
</script>
