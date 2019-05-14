<?php

namespace app\assets;

use yii\web\AssetBundle;

class TextFillAsset extends AssetBundle
{
    public $sourcePath = '@bower/jquery-textfill';
    public $js = [
        'dist/jquery.textfill.min.js',
    ];
}
