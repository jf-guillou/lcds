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
    public static $typeDescription = 'Display weather for a given city.';
    public static $html = '<div class="weather">%data%</div>';
    public static $input = 'url';
    public static $output = 'text';
    public static $usable = true;
    public static $preview = null;

    /**
     * Power by DarkSky : https://darksky.net/poweredby/.
     */

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
        $data = str_replace('%apikey%', Yii::$app->params['weatherApiKey'], $data);

        // Fetch content from cache if possible
        $content = self::fromCache($data);
        if (!$content) {
            $content = self::downloadFeed($data);
            self::toCache($data, $content, self::BASE_CACHE_TIME);
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
