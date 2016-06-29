<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
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
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'background',
            'css:ntext',
        ],
    ]) ?>

    <div class="design-wrapper">
        <img src="<?= $background; ?>" alt="Background" class="img-full background-edit" />
        <div id="design" class="design"></div>
    </div>

</div>

<script type="text/javascript">
var fields = <?= json_encode(array_map(function ($f) {
    return $f->toArray();
}, $fields)) ?>;
var fieldUrl = '<?= $fieldUrl ?>';
</script>
