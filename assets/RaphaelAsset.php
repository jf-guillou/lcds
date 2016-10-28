<?php

namespace app\assets;

use yii\web\AssetBundle;

class RaphaelAsset extends AssetBundle
{
    public $sourcePath = '@bower/raphael';
    public $js = [
        'raphael.min.js',
    ];
}
