<?php

namespace App\Core;

use Exception;

class RequestFactory {

    /**
     * @throws Exception
     */
    public function create(string $className) {
        if (!class_exists($className)) {
            throw new Exception("Фабрика не может создать несуществующий dto класс: $className");
        }

        return new $className($_GET, $_POST, $_FILES);
    }

}