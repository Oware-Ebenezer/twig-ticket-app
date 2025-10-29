<?php

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

// Setup Twig
$twig = Twig::create(__DIR__ . '/templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

return $app;
