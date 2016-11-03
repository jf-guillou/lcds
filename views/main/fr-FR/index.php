<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'LCDS';
?>
<div class="main-index">
    <h1><?= $this->title ?></h1>
    <h2>Light Centralized Digital Signage</h2>

    <p>
        Cette application vous permet de créer des écrans simples destinés à être affichés sur n'importe quel équipement possédant un navigateur internet.
    </p>

    <div class="row">
        <?php
        if (Yii::$app->user->can('setDevices')) : ?>
        <div class="col-lg-4 text-center">
            <h3><?= Html::a('Diffuseur', ['/device']) ?></h3>
            <h4>Gestion des équipements physiques</h4>
            <ul>
                <li>Activation et désactivation des diffuseurs</li>
                <li>Modification des noms et descriptions</li>
                <li>Liaison avec les écrans</li>
                <li>Suppression de diffuseurs</li>
            </ul>
        </div>
        <?php
        endif;
        if (Yii::$app->user->can('setTemplates')) : ?>
        <div class="col-lg-4 text-center">
            <h3><?= Html::a('Modèle', ['/screen-template']) ?></h3>
            <h4>Création et édition des gabarits</h4>
            <ul>
                <li>Création de modèles</li>
                <li>Modification des champs</li>
                <li>Adjonction d'images de fond</li>
            </ul>
        </div>
        <?php
        endif;
        if (Yii::$app->user->can('setScreens')) : ?>
        <div class="col-lg-4 text-center">
            <h3><?= Html::a('Ecran', ['/screen']) ?></h3>
            <h4>Intégration des modèles et flux aux diffuseurs</h4>
            <ul>
                <li>Prévisualisation des écrans</li>
                <li>Intégration des modèles</li>
                <li>Modification de la durée</li>
                <li>Ajout des flux</li>
            </ul>
        </div>
        <?php
        endif;
        if (Yii::$app->user->can('setOwnFlowContent')) : ?>
        <div class="col-lg-4 text-center">
            <h3><?= Html::a('Flux', ['/flow']) ?></h3>
            <h4>Création et ajout de contenu aux écrans</h4>
            <ul>
                <li>Création assistée de contenu</li>
                <li>Activation et désactivation de contenu</li>
            </ul>
        </div>
        <?php
        endif;
        if (Yii::$app->user->can('setContent')) : ?>
        <div class="col-lg-4 text-center">
            <h3><?= Html::a('Contenu', ['/content']) ?></h3>
            <h4>Gestion globale de l'ensemble des contenus</h4>
            <ul>
                <li>Modification avancée</li>
            </ul>
        </div>
        <?php
        endif;
        if (Yii::$app->user->can('admin')) : ?>
        <div class="col-lg-4 text-center">
            <h3><?= Html::a('Utilisateurs', ['/user']) ?></h3>
            <h4>Gestion des droits d'accès utilisateurs</h4>
            <ul>
                <li>Création et import d'utilisateurs</li>
                <li>Ajout de droits d'accès et de modification</li>
                <li>Association utilisateurs / flux</li>
            </ul>
        </div>
        <?php
        endif; ?>
    </div>

    <br /><br />
    <div class="row text-center">
        <h3>Créer un nouveau diffuseur</h3>
        <h4>Affecter cette adresse comme page d'accueil dans le navigateur web de l'équipement</h4>
        <h4><?= Html::a(Url::to(['/frontend'], true), ['/frontend']) ?></h4>
    </div>
</div>
