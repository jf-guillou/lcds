<?php

namespace app\models\types;

use app\models\Content;

/**
 * This is the model class for content uploads.
 */
class DateTime extends Content
{
    public static $typeName = 'Date&Time';
    public static $selfUpdate = true;
    public static $html = '<span class="datetime"></span>';
    public static $css = '%field% { text-align: center; vertical-align: middle; }';
    public static $kind = 'raw';
    public static $usable = true;
    public static $js = <<<'EOT'
{
  var $f = $('%field%');
  if (!$f.length) { return; }
  var $d = $f.find('.date');
  var $t = $f.find('.time');
  var $dt = $f.find('.datetime');
  var dF = '';
  function setDateTime() {
    var m = moment();
    $d.html(m.format('DD/MM/YYYY'));
    $t.html(m.format('HH:mm:ss'));
    $dt.html(m.format('DD/MM/YYYY<br />HH:mm:ss'));
  }
  setInterval(setDateTime, 1000);
  setDateTime();
  $f.show();
  $f.textfill({maxFontPixels: 0});
}
EOT;
}
