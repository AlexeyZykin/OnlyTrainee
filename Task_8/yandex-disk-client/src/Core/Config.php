<?php

namespace App\Core;

class Config
{

    private array $data;

    private static ?self $instance = null;


    private function __construct()
    {
        $envFilePath = dirname(__DIR__, 2) . '/.env';

        if (file_exists($envFilePath)) {
            $this->data = parse_ini_file($envFilePath);
        }
    }


    public static function getInstance(): Config
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public function YANDEX_DISK_TOKEN(): string
    {
        return $this->data['YANDEX_DISK_TOKEN'] ?? '';
    }

    public function getBasePath(): string
    {
        return $this->data['YANDEX_BASE_PATH'] ?? 'app:/';
    }
}