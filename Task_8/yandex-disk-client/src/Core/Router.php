<?php

namespace App\Core;

use App\Exception\NotFoundException;
use App\Model\HttpMethod;
use Exception;

class Router
{

    private array $routes;


    public function __construct(private readonly RequestFactory $requestFactory)
    {
    }


    public function register(
        HttpMethod $httpMethod,
        string $path,
        callable $callback,
        ?string $className = null
    ): void
    {
        $this->routes[$httpMethod->value][$path] = [
            'callback' => $callback,
            'className' => $className
        ];
    }


    /**
     * @throws Exception
     */
    public function handleRequest(string $reqMethod, string $uri): void
    {
        $path = parse_url($uri)["path"];

        if (!isset($this->routes[$reqMethod]) || !isset($this->routes[$reqMethod][$path])) {
            throw new NotFoundException("Данный path не найден: $path");
        }

        $route = $this->routes[$reqMethod][$path];
        $callback = $route['callback'];
        if (!$route['className']) {
            call_user_func($callback);
            exit;
        }

        $request = $this->requestFactory->create($route['className']);
        call_user_func($callback, $request);
    }
}