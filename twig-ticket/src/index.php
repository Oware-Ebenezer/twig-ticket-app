<?php

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';


$container = new Container();
AppFactory::setContainer($container);


$app = AppFactory::create();


$twig = Twig::create(__DIR__ . '/templates', ['cache' => false]);
$container->set('view', $twig);


$app->add(TwigMiddleware::create($app, $twig));

$app->addRoutingMiddleware();

(require __DIR__ . '/routes.php')($app);


$app->run();
