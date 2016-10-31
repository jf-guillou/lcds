<?php

namespace lcds\composer;

class Installer
{
    /**
     * Post composer install event script.
     *
     * @param mixed $event composer event
     */
    public static function postInstall($event)
    {
        $params = $event->getComposer()->getPackage()->getExtra();
        if (isset($params[__METHOD__]) && is_array($params[__METHOD__])) {
            foreach ($params[__METHOD__] as $method => $args) {
                call_user_func_array([__CLASS__, $method], (array) $args);
            }
        }
    }

    /**
     * Copy configuration files on composer install script end.
     *
     * @param array $paths configuration files
     */
    public static function copyConfiguration(array $paths)
    {
        foreach ($paths as $from => $to) {
            echo 'copy '.$from.' '.$to.': ';
            if (is_dir($to) || is_file($to)) {
                echo 'destination file exists.'.PHP_EOL;
            } elseif (is_dir($from) || is_file($from)) {
                if (copy($from, $to)) {
                    echo 'done.'.PHP_EOL;
                } else {
                    echo 'error while copying file.'.PHP_EOL;
                }
            } else {
                echo 'file not found.'.PHP_EOL;
            }
        }
    }
}
