<?php
use app\assets\FrontendErrAsset;

FrontendErrAsset::register($this);
?>

<div class="gigantic text-center">
    <p>CET DIFFUSEUR N'A PAS ENCORE ETE AUTORISE</p>
    <p>MERCI DE VOUS RENDRE SUR</p>
    <p><a href="<?= $url ?>"><?= $url ?></a></p>
    <p>ET ACTIVEZ CET DIFFUSEUR</p>
</div>

<div class="huge text-center">
    <p>Le navigateur se rafraîchira automatiquement</p>
</div>