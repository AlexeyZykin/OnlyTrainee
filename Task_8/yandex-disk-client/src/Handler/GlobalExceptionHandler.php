<?php

namespace App\Handler;

use App\Exception\NotFoundException;
use App\Model\LogLevel;
use App\Util\Logger;
use Throwable;

class GlobalExceptionHandler {

    public function handleError(int $errno, string $errstr, string $errfile = null, int $errline = null): void {
        $message = sprintf("Error [%d]: %s in %s on line %d", $errno, $errstr, $errfile, $errline);
        Logger::log($message, LogLevel::ERROR);

        http_response_code(500);

        echo "<h1>Ошибка на сервере</h1>";
        exit;
    }

    public function handleException(Throwable $e): void {
        if ($e instanceof NotFoundException) {
            $this->handleNotFoundException($e);
        } else {
            $this->handleBaseException($e);
        }
    }

    private function handleNotFoundException(NotFoundException $e): void {
        Logger::log($e->getMessage(), LogLevel::WARN);

        http_response_code(404);

        echo "<h1>404 - Not Found</h1>";
        exit;
    }

    private function handleBaseException(Throwable $ex): void {
        Logger::log($ex->getMessage(), LogLevel::ERROR);

        http_response_code(500);

        echo "<h1>Ошибка на сервере</h1>";
        exit;
    }

}