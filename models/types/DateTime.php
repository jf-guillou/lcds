<?php

namespace app\models\types;

use Yii;
use app\models\ContentType;

/**
 * This is the model class for DateTime content type.
 */
class DateTime extends ContentType
{
    public $selfUpdate = true;
    public $html = '<span class="datetime"></span>';
    public $css = '%field% { text-align: center; vertical-align: middle; }';
    public $input = 'none';
    public $output = 'raw';
    public $usable = true;
    public $js = <<<'EOT'
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

    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->name = Yii::t('app', 'Date&Time');
    }
}
