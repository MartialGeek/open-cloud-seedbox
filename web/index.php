<?php

use GuzzleHttp\Client as GuzzleClient;
use Martial\Warez\Front\Controller\HomeController;
use Martial\Warez\T411\Api\Client;
use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';

$debug = true;
$env = 'dev';

$app = new Application();
$app['debug'] = $debug;
$app['env'] = $env;

$app
    ->register(new ServiceControllerServiceProvider())
    ->register(new TwigServiceProvider(), [
        'twig.options' => [
            'cache' => __DIR__ . '/../var/cache/twig'
        ]
    ])
    ->register(new \Silex\Provider\MonologServiceProvider(), [
        'monolog.logfile' => __DIR__ . '/../var/log/' . $app['env'] . '.log'
    ]);

$app['twig.loader.filesystem']->setPaths([
    __DIR__ . '/../src/Front/View/Home'
], 'home');

$app['t411.api.http_client'] = $app->share(function() {
    return new GuzzleClient([
        'base_url' => 'https://api.t411.me'
    ]);
});

$app['t411.api.client'] = $app->share(function() use ($app) {
    return new Client($app['t411.api.http_client']);
});

$app['home.controller'] = $app->share(function() use ($app) {
    return new HomeController($app['twig']);
});

$app->get('/', 'home.controller:index');

$app->run();
