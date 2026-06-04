<?php

use App\Controller\FileController;
use App\Core\Config;
use App\Core\RequestFactory;
use App\Core\Router;
use App\Core\View;
use App\Handler\GlobalExceptionHandler;
use App\Model\Dto\DeleteFileRequest;
use App\Model\Dto\GetFileRequest;
use App\Model\Dto\UpdateFileRequest;
use App\Model\Dto\UploadFileRequest;
use App\Model\HttpMethod;
use App\Service\FileService;
use App\Service\SessionService;
use Arhitector\Yandex\Disk;

require_once(__DIR__ . '/../vendor/autoload.php');

$globalExceptionHandler = new GlobalExceptionHandler();
set_exception_handler([$globalExceptionHandler, 'handleException']);
set_error_handler([$globalExceptionHandler, 'handleError']);

$reqFactory = new RequestFactory();
$router = new Router($reqFactory);

$config = Config::getInstance();
$diskClient = new Disk($config->YANDEX_DISK_TOKEN());

$view = new View();

$fileService = new FileService($diskClient);
$sessionService = new SessionService();

$fileController = new FileController($fileService, $view, $sessionService);

$router->register(
    httpMethod: HttpMethod::GET,
    path: '/',
    callback: [$fileController, 'index']
);
$router->register(
    httpMethod: HttpMethod::GET,
    path: "/get",
    callback: [$fileController, 'getFile'],
    className: GetFileRequest::class
);
$router->register(
    httpMethod: HttpMethod::POST,
    path: "/upload",
    callback: [$fileController, 'uploadFile'],
    className: UploadFileRequest::class
);
$router->register(
    httpMethod: HttpMethod::POST,
    path: "/delete",
    callback: [$fileController, 'deleteFile'],
    className: DeleteFileRequest::class
);
$router->register(
    httpMethod: HttpMethod::POST,
    path: "/update",
    callback: [$fileController, 'updateFile'],
    className: UpdateFileRequest::class
);

$router->handleRequest($_SERVER["REQUEST_METHOD"], $_SERVER["REQUEST_URI"]);