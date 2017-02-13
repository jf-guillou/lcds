<?php

if (!defined('YII_DEBUG')) {
    define('YII_DEBUG', getenv('YII_DEBUG') ?: false);
}
if (!defined('YII_ENV')) {
    define('YII_ENV', getenv('YII_ENV') ?: 'prod');
}

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__.'/../config/web.php';

(new yii\web\Application($config))->run();
