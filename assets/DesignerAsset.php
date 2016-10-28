<?php

namespace app\assets;

use yii\web\AssetBundle;

class DesignerAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/designer.css',
    ];
    public $js = [
        'js/designer.js',
    ];
    public $depends = [
        'app\assets\AppAsset',
        'app\assets\RaphaelAsset',
    ];
}
