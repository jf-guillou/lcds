<?php

namespace app\models\types;

/**
 * This is the model class for content uploads.
 */
class Agenda extends RSS
{
    public static $typeName = 'Agenda';
    public static $typeDescription = 'Display an agenda from an RSS feed.';
    public static $html = '<div class="agenda" data-url="%data%"></div>';
}
