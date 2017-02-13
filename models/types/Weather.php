<?php

namespace app\models\types;

use Yii;
use app\models\ContentType;

/**
 * This is the model class for Weather content type.
 */
class Weather extends ContentType
{
    const BASE_CACHE_TIME = 600;
    const URL = 'https://api.darksky.net/forecast/%apikey%/%data%?lang=%lang%&units=%units%&exclude=hourly,daily,alerts';

    public $html = '<div class="weather">%data%</div>';
    public $css = '%field% { text-align: center; } %field% .wi { font-size: 0.8em; }';
    public $js = '';
    public $input = 'latlong';
    public $output = 'text';
    public $usable = true;
    public $exemple = '@web/images/weather.preview.jpg';
    public $canPreview = true;

    private $opts;

    const ICONS = [
        'clear-day' => 'wi-day-sunny',
        'clear-night' => 'wi-night-clear',
        'rain' => 'wi-rain',
        'snow' => 'wi-snow',
        'sleet' => 'wi-sleet',
        'wind' => 'wi-strong-wind',
        'fog' => 'wi-fog',
        'cloudy' => 'wi-cloudy',
        'partly-cloudy-day' => 'wi-day-cloudy',
        'partly-cloudy-night' => 'wi-night-cloudy',
        'hail' => 'wi-hail',
        'thunderstorm' => 'wi-lightning',
        'tornado' => 'wi-tornado',
    ];

    /**
     * Power by DarkSky : https://darksky.net/poweredby/.
     */

    /**
     * {@inheritdoc}
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->name = Yii::t('app', 'Weather');
        $this->description = Yii::t('app', 'Display weather for given coordinates.');
    }

    /**
     * {@inheritdoc}
     */
    public function contentRules()
    {
        return [
            [['data'], 'match', 'pattern' => '/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function processData($data)
    {
        $this->opts = Yii::$app->params['weather'];

        $url = str_replace([
            '%apikey%',
            '%data%',
            '%lang%',
            '%units%',
        ], [
            $this->opts['apikey'],
            $data,
            $this->opts['language'],
            $this->opts['units'],
        ], self::URL);

        // Fetch content from cache if possible
        $content = self::fromCache($url);
        if ($content === false) {
            $content = self::downloadContent($url);
            self::toCache($url, $content);
        }

        return $this->format(json_decode($content));
    }

    protected function format($w)
    {
        if (!$w || !$w->currently) {
            return Yii::t('app', 'Weather unavailable');
        }
        $c = $w->currently;
        $summary = $this->opts['withSummary'] ? $c->summary : '';
        $icon = array_key_exists($c->icon, self::ICONS) ? self::ICONS[$c->icon] : 'wi-na';
        $temp = round($c->temperature, 1);
        $tUnit = Yii::t('app', '°F');

        return <<<EOD
<span class="weather-content bigtext">
    <span class="weather-summary">{$summary}</span>
    <span class="wi $icon" />
    <span class="weather-temp">{$temp}{$tUnit}</span>
</span>
</div>
EOD;
    }
}
