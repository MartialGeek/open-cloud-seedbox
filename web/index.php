<?php

use Martial\Warez\Application\Bootstrap;
use Silex\Application;

require_once __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/app.php';
$app = new Application();
$bootstrap = new Bootstrap($app, $config, 'dev');

$bootstrap
    ->registerControllers([
        'home.controller' => [
            'class' => '\Martial\Warez\Front\Controller\HomeController'
        ],
        'user.controller' => [
            'class' => '\Martial\Warez\Front\Controller\UserController',
            'dependencies' => [
                $app['user.service'],
                $app['profile.service']
            ]
        ],
        'security.controller' => [
            'class' => '\Martial\Warez\Front\Controller\SecurityController'
        ]
    ]);

$app
    ->get('/', 'home.controller:index')
    ->bind('homepage');

$app
    ->get('/user/profile', 'user.controller:profile')
    ->bind('user_profile');

$app
    ->post('/login', 'user.controller:login')
    ->bind('login');

$app
    ->get('/form-login', 'security.controller:loginForm')
    ->bind('form_login');

$app
    ->get('/logout', 'user.controller:logout')
    ->bind('logout');

$app->run();
