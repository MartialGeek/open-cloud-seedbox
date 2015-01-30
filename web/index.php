<?php

use Martial\Warez\Front\Controller\HomeController;
use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';

$debug = true;
$app = new Application();
$app['debug'] = $debug;

$app
    ->register(new ServiceControllerServiceProvider())
    ->register(new TwigServiceProvider(), [
        'twig.options' => [
            'cache' => __DIR__ . '/../var/cache/twig'
        ]
    ]);

$app['twig.loader.filesystem']->setPaths([
    __DIR__ . '/../src/Front/View/Home'
], 'home');

$app['home.controller'] = $app->share(function() use ($app) {
    return new HomeController($app['twig']);
});

$app->get('/', 'home.controller:index');

$app->run();
