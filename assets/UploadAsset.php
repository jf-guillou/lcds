<?php

namespace app\assets;

use yii\web\AssetBundle;

class UploadAsset extends AssetBundle
{
    public $sourcePath = '@bower/jquery-file-upload';
    public $js = [
        'js/jquery.fileupload.js',
    ];
    public $depends = [
        'yii\jui\JuiAsset',
    ];
}
