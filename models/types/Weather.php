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
    public static $typeName = 'Weather';
    public static $typeDescription = 'Display weather for given coordinates.';
    public static $html = '<div class="weather">%data%</div>';
    public static $input = 'latlong';
    public static $output = 'text';
    public static $usable = true;
    public static $preview = null;

    const URL = 'https://api.darksky.net/forecast/%apikey%/%data%?lang=%lang%&units=%units%&exclude=hourly,daily,alerts';

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
        $icon = Url::to('@web/images/weather/'.$c->icon.'.svg');
        $temp = round($c->temperature, 1);
        $tUnit = Yii::t('app', '°F');

        return <<<EOD
<span class="weather-summary">{$c->summary}</span>
<div class="weather-details">
<img src="{$icon}" class="weather-icon" alt="{$c->summary}" />
<span class="weather-temp">{$temp}{$tUnit}</span>
</div>
EOD;
    }
}
