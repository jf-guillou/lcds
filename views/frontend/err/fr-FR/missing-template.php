<?php
use app\assets\FrontendErrAsset;

FrontendErrAsset::register($this);
?>

<div class="gigantic text-center">
    <p>CET ECRAN N'A PAS ENCORE DE MODÈLE</p>
    <p>MERCI DE VOUS RENDRE SUR</p>
    <p><a href="<?= $templateUrl ?>"><?= $templateUrl ?></a></p>
    <p>ET CONFIGUREZ CET ECRAN</p>
</div>

<div class="huge text-center">
    <p>Le navigateur se rafraîchira automatiquement</p>
</div>
