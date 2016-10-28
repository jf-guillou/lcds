<?php

namespace app\assets;

use yii\web\AssetBundle;

class WeatherAsset extends AssetBundle
{
    public $sourcePath = '@bower/weather-icons';
    public $css = [
        'css/weather-icons.min.css',
    ];
}
