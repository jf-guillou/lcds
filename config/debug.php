<?php

if (!defined('YII_DEBUG')) {
    define('YII_DEBUG', getenv('YII_DEBUG') ?: false);
}
if (!defined('YII_ENV')) {
    define('YII_ENV', getenv('YII_ENV') ?: 'prod');
}
