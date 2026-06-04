<?php

namespace App\Util;

use App\Model\LogLevel;

class Logger
{

    private function __construct()
    {
    }

    public static function log($message, $logLevel = LogLevel::INFO): void
    {
        $dir = dirname(__DIR__, 2) . '/log/';
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        $log = date('Y-m-d H:i:s') . '; ' . $logLevel->value . '; ' . $message;
        file_put_contents($dir . 'log.txt', $log . PHP_EOL, FILE_APPEND);
    }

}