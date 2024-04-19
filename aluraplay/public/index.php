<?php

declare(strict_types=1);

use Alura\Mvc\Controller\{
    Error404Controller,
};
use Alura\Mvc\Repository\VideoRepository;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

require_once __DIR__ . '/../vendor/autoload.php';

// $dbPath = __DIR__ . '/../banco.sqlite';
// $pdo = new PDO("sqlite:$dbPath");
// $videoRepository = new VideoRepository($pdo);

$routes = require_once __DIR__ . '/../config/routes.php';

/** @var ContainerInterface $diContainer */
$diContainer = require_once __DIR__ . '/../config/dependences.php';

$pathInfo = $_SERVER['PATH_INFO'] ?? '/';
$httpMethod = $_SERVER['REQUEST_METHOD'];

session_start();
session_regenerate_id();
$isLoginRoute = $pathInfo === '/login';
if (!array_key_exists('logado', $_SESSION) && !$isLoginRoute) {
    header('Location: /login');
    return;
}

$key = "$httpMethod|$pathInfo";
if (array_key_exists($key, $routes)) {
    $controllerClass = $routes["$httpMethod|$pathInfo"];

    // $controller = new $controllerClass($videoRepository);
    $controller = $diContainer->get($controllerClass);
} else {
    $controller = new Error404Controller();
}

$psr17Factory = new Psr17Factory();

$creator = new ServerRequestCreator(
    $psr17Factory,
    $psr17Factory,
    $psr17Factory,
    $psr17Factory,
);

$request = $creator->fromGlobals();

/** @var RequestHandlerInterface $controller */
$response = $controller->handle($request);

http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {  
        header (sprintf('%s: %s', $name, $value), false);
    }
}

echo $response->getBody();