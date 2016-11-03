<?php


/* @var $this yii\web\View */

$this->title = 'LCDS';
?>
<div class="main-index">
    <h1><?= $this->title ?></h1>
    <h2>Light Centralized Digital Signage</h2>

    <p>
        This application allows you to setup simple screens to be displayed of any device with a web browser.
    </p>

    <div class="row">
        <?php
        if (Yii::$app->user->can('setDevices')) : ?>
        <div class="col-lg-4 text-center">
            <h3><?= Html::a('Device', ['/device']) ?></h3>
            <h4>Manage physical equipments</h4>
            <ul>
                <li>Toggle devices</li>
                <li>Modify names and descriptions</li>
                <li>Link with screens</li>
                <li>Delete devices</li>
            </ul>
        </div>
        <?php
        endif;
        if (Yii::$app->user->can('setTemplates')) : ?>
        <div class="col-lg-4 text-center">
            <h3><?= Html::a('Template', ['/screen-template']) ?></h3>
            <h4>Create and edit templates</h4>
            <ul>
                <li>Create template</li>
                <li>Edit fields</li>
                <li>Add backgrounds</li>
            </ul>
        </div>
        <?php
        endif;
        if (Yii::$app->user->can('setScreens')) : ?>
        <div class="col-lg-4 text-center">
            <h3><?= Html::a('Screen', ['/screen']) ?></h3>
            <h4>Integrate flow and templates to devices</h4>
            <ul>
                <li>Screen preview</li>
                <li>Integrate template</li>
                <li>Edit duration</li>
                <li>Add flows</li>
            </ul>
        </div>
        <?php
        endif;
        if (Yii::$app->user->can('setOwnFlowContent')) : ?>
        <div class="col-lg-4 text-center">
            <h3><?= Html::a('Flow', ['/flow']) ?></h3>
            <h4>Create and add content to screens</h4>
            <ul>
                <li>Assisted content creation</li>
                <li>Toggle content display</li>
            </ul>
        </div>
        <?php
        endif;
        if (Yii::$app->user->can('setContent')) : ?>
        <div class="col-lg-4 text-center">
            <h3><?= Html::a('Content', ['/content']) ?></h3>
            <h4>Content global management</h4>
            <ul>
                <li>Advanced edition</li>
            </ul>
        </div>
        <?php
        endif;
        if (Yii::$app->user->can('admin')) : ?>
        <div class="col-lg-4 text-center">
            <h3><?= Html::a('Users', ['/user']) ?></h3>
            <h4>Handle user rights</h4>
            <ul>
                <li>Create and import users</li>
                <li>Add access and modification rights</li>
                <li>Associate users and flows</li>
            </ul>
        </div>
        <?php
        endif; ?>
    </div>

    <br /><br />
    <div class="row text-center">
        <h3>Add a new device</h3>
        <h4>Setup device browser with this default homepage url</h4>
        <h4><?= Html::a(Url::to(['/frontend'], true), ['/frontend']) ?></h4>
    </div>
</div>
