<?php

namespace app\models\types;

use Yii;
use app\models\Content;
use yii\helpers\Url;

/**
 * This is the model class for Weather content type.
 */
class Weather extends Content
{
    const BASE_CACHE_TIME = 600;
    const URL = 'https://api.darksky.net/forecast/%apikey%/%data%?lang=%lang%&units=%units%&exclude=hourly,daily,alerts';

    public static $typeName = 'Weather';
    public static $typeDescription = 'Display weather for given coordinates.';
    public static $html = '<div class="weather">%data%</div>';
    public static $css = '%field% { text-align: center; } %field% .wi { font-size: 0.8em; }';
    public static $js = '';
    public static $input = 'latlong';
    public static $output = 'text';
    public static $usable = true;
    public static $preview = '@web/images/weather.preview.jpg';

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
    public function rules()
    {
        $rules = parent::rules();
        foreach ($rules as $i => $r) {
            if (in_array('data', $r[0])) {
                $rules[$i] = [['data'], 'match', 'pattern' => '/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/'];
            }
        }

        return $rules;
    }

    /**
     * Downloads feed from URL through proxy if necessary.
     *
     * @param string $url
     *
     * @return string feed content
     */
    protected static function downloadFeed($url)
    {
        if (\Yii::$app->params['proxy']) {
            $ctx = [
                'http' => [
                    'proxy' => 'tcp://vdebian:8080',
                    'request_fulluri' => true,
                ],
            ];

            return file_get_contents(urldecode($url), false, stream_context_create($ctx));
        } else {
            return file_get_contents(urldecode($url));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processData($data)
    {
        $url = str_replace([
            '%apikey%',
            '%data%',
            '%lang%',
            '%units%',
        ], [
            Yii::$app->params['weather']['apikey'],
            $data,
            Yii::$app->params['weather']['language'],
            Yii::$app->params['weather']['units'],
        ], self::URL);

        // Fetch content from cache if possible
        $content = self::fromCache($url);
        if (!$content) {
            $content = self::downloadFeed($url);
            self::toCache($url, $content, self::BASE_CACHE_TIME);
        }

        return $this->format(json_decode($content));
    }

    protected function format($w)
    {
        if (!$w || !$w->currently) {
            return Yii::t('app', 'Weather unavailable');
        }
        $c = $w->currently;
        $icon = array_key_exists($c->icon, self::ICONS) ? self::ICONS[$c->icon] : 'wi-na';
        $temp = round($c->temperature, 1);
        $tUnit = Yii::t('app', '°F');

        return <<<EOD
<span class="weather-content">
    <span class="weather-summary">{$c->summary}</span>
    <span class="wi $icon" />
    <span class="weather-temp">{$temp}{$tUnit}</span>
</span>
</div>
EOD;
    }
}
