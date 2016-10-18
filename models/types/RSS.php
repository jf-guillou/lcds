<?php

namespace app\models\types;

use app\models\Content;

/**
 * This is the model class for RSS content type.
 */
class RSS extends Content
{
    public static $typeName = 'RSS';
    public static $typeDescription = 'Display an RSS feed inline.';
    public static $html = '<div class="rss" data-url="%data%"></div>';
    public static $input = 'url';
    public static $output = null;
    public static $usable = false;
}
