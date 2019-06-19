<?php

use App\Models\User;
use Slim\Container;
use Slim\Http\Uri;
use Slim\Views\Twig;
use App\Validation\Validator;

$container = $app->getContainer();

/*********** PDO  ***********/

/**
 * @return PDO
 */
$container['pdo'] = function () {
    $db = require __DIR__ . '/../config/DatabaseDemo.php';
    $pdo = new PDO("{$db['type']}:dbname={$db['name']};host={$db['host']}", $db['user'], $db['pass'], [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $pdo;
};

/*********** Models  ***********/

/**
 * @param Container $container
 * @return User
 */
$container['user'] = function (Container $container) {
    $user = new User($container);

    return $user;
};

/*********** Twig View  ***********/

/**
 * @param Container $container
 * @return Twig
 */
$container['view'] = function (Container $container) {
    $cache = ($container->get('settings')['cache']) ? dirname(__DIR__) . '/tmp/cache' : false;
    $debug = ($container->get('settings')['debug']) ? true : false;

    $view = new Twig(dirname(__DIR__) . '/app/Views', [
        'cache' => $cache,
        'debug' => $debug
    ]);

    $router = $container->get('router');
    $uri = Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));
    $view->addExtension(new Twig_Extension_Debug());
    $view->addExtension(new Twig_Extensions_Extension_Date());

    return $view;
};

/*********** Validator  ***********/

/**
 * @return Validator
 */
$container['validator'] = function () {
    return new Validator;
};
