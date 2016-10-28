<?php

namespace app\assets;

use yii\web\AssetBundle;

class FrontendErrAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/frontenderr.css',
    ];
    public $js = [
        'js/frontenderr.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
