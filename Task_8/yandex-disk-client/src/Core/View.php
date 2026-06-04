<?php

namespace App\Core;

class View
{
    function render(string $template, array $data = []): void {
        $requiredTemplate = dirname(__DIR__) . '/View/' . $template . ".php";

        extract($data);

        require_once $requiredTemplate;
    }
}