<?php

use GuzzleHttp\Client as GuzzleClient;
use Martial\Warez\Front\Controller\HomeController;
use Martial\Warez\Front\Controller\UserController;
use Martial\Warez\T411\Api\Data\DataTransformer;
use Martial\Warez\T411\Api\Client;
use Silex\Application;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/app.php';

$app = new Application();
$app['debug'] = CONFIG_PROJECT_ENV == 'dev';
$app['env'] = CONFIG_PROJECT_ENV;

$app
    ->register(new ServiceControllerServiceProvider())
    ->register(new TwigServiceProvider(), $config['twig'])
    ->register(new MonologServiceProvider(), $config['monolog'])
    ->register(new SessionServiceProvider())
    ->register(new FormServiceProvider())
    ->register(new ValidatorServiceProvider())
    ->register(new TranslationServiceProvider(), $config['translator'])
    ->register(new UrlGeneratorServiceProvider())
    ->register(new \Silex\Provider\DoctrineServiceProvider(), $config['doctrine']['dbal']);

$app['twig.loader.filesystem']->setPaths([
    __DIR__ . '/../src/Front/View/Home'
], 'home');

$app['t411.api.http_client'] = $app->share(function() {
    return new GuzzleClient([
        'base_url' => 'https://api.t411.me'
    ]);
});

$app['t411.api.data.data_transformer'] = $app->share(function() {
    return new DataTransformer();
});

$app['t411.api.client'] = $app->share(function() use ($app) {
    return new Client(
        $app['t411.api.http_client'],
        $app['t411.api.data.data_transformer']
    );
});

$app['home.controller'] = $app->share(function() use ($app) {
    return new HomeController($app['twig'], $app['form.factory'], $app['session'], $app['url_generator']);
});

$app['user.controller'] = $app->share(function() use ($app) {
    return new UserController($app['twig'], $app['form.factory'], $app['session'], $app['url_generator']);
});

$app
    ->get('/', 'home.controller:index')
    ->bind('homepage');

$app
    ->post('/login', 'user.controller:login')
    ->bind('login');

$app
    ->get('/logout', 'user.controller:logout')
    ->bind('logout');

$app->run();
