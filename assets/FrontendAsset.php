<?php

namespace app\assets;

use yii\web\AssetBundle;

class FrontendAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/frontend.css',
    ];
    public $js = [
        'js/frontend.js',
    ];
    public $depends = [
        'app\assets\AppAsset',
        'app\assets\WeatherAsset',
        'app\assets\TextFillAsset',
        'app\assets\MomentAsset',
    ];
}
