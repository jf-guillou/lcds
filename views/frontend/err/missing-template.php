<?php
use app\assets\FrontendErrAsset;

FrontendErrAsset::register($this);
?>

<div class="gigantic text-center">
    <p>THIS SCREEN HAS NO TEMPLATE YET</p>
    <p>PLEASE GO TO</p>
    <p><a href="<?= $templateUrl ?>"><?= $templateUrl ?></a></p>
    <p>AND SETUP THIS SCREEN</p>
</div>

<div class="huge text-center">
    <p>The browser will automatically refresh shortly after</p>
</div>
