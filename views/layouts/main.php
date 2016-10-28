<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<script type="text/javascript">window.jqReady = [];</script>
<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::t('app', 'LCDS'),
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            ['label' => Yii::t('app', 'Devices'), 'url' => ['/device'], 'visible' => Yii::$app->user->can('setDevices')],
            ['label' => Yii::t('app', 'Templates'), 'url' => ['/screen-template'], 'visible' => Yii::$app->user->can('setTemplates')],
            ['label' => Yii::t('app', 'Screens'), 'url' => ['/screen'], 'visible' => Yii::$app->user->can('setScreens')],
            ['label' => Yii::t('app', 'Flows'), 'url' => ['/flow'], 'visible' => Yii::$app->user->can('setOwnFlowContent')],
            ['label' => Yii::t('app', 'Content'), 'url' => ['/content'], 'visible' => Yii::$app->user->can('setOwnFlowContent')],
            ['label' => Yii::t('app', 'Users'), 'url' => ['/user'], 'visible' => Yii::$app->user->can('admin')],
            Yii::$app->user->isGuest ? (
                ['label' => Yii::t('app', 'Login'), 'url' => ['/auth/login']]
            ) : (
                ['label' => Yii::t('app', 'Logout ({username})', ['username' => Yii::$app->user->identity->username]), 'url' => ['/auth/logout']]
            ),
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; My Company <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
<script type="text/javascript">
if (window.hasOwnProperty('jqReady')) {
    $(function() {
        window.jqReady.forEach(function(f) {
            f();
        });
    });
}
</script>
</body>
</html>
<?php $this->endPage() ?>
