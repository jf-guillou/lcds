<?php

namespace app\helpers;

use Yii;

class Alert
{
    const INFO = 'info';
    const SUCCESS = 'success';
    const WARNING = 'warning';
    const DANGER = 'danger';
    const ALERT_TYPES = [
        self::INFO,
        self::SUCCESS,
        self::WARNING,
        self::DANGER,
    ];

    /**
     * Create a new flash alert used for displaying info to users.
     *
     * @param string $message alert message content
     * @param string $type    alert type
     */
    public static function add($message, $type = self::INFO)
    {
        if (in_array($type, self::ALERT_TYPES)) {
            Yii::$app->session->addFlash('alert', ['type' => $type, 'message' => Yii::t('app', $message)]);

            return true;
        }

        return false;
    }

    /**
     * Retrieve previously added alerts to display them.
     *
     * @return array alerts
     */
    public static function getAlerts()
    {
        return Yii::$app->session->getFlash('alert', []);
    }
}
