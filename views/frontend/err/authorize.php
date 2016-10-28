<?php
use app\assets\FrontendErrAsset;

FrontendErrAsset::register($this);
?>

<div class="gigantic text-center">
    <p>THIS DEVICE HAS NOT YET BEEN AUTHORIZED</p>
    <p>PLEASE GO TO</p>
    <p><a href="<?= $url ?>"><?= $url ?></a></p>
    <p>AND ACTIVATE THIS DEVICE</p>
</div>

<div class="huge text-center">
    <p>The browser will automatically refresh shortly after</p>
</div>
