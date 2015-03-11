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
                $app['user.service']
            ]
        ],
        'security.controller' => [
            'class' => '\Martial\Warez\Front\Controller\SecurityController'
        ],
        'tracker.controller' => [
            'class' => '\Martial\Warez\Front\Controller\TrackerController',
            'dependencies' => [
                $app['t411.api.client'],
                $app['user.service'],
                $app['profile.service']
            ]
        ]
    ]);

$app
    ->get('/', 'home.controller:index')
    ->bind('homepage');

$app
    ->get('/user/profile', 'user.controller:profile')
    ->bind('user_profile');

$app
    ->post('/user/profile/update', 'user.controller:profileUpdate')
    ->bind('user_profile_update');

$app
    ->post('/login', 'user.controller:login')
    ->bind('login');

$app
    ->get('/form-login', 'security.controller:loginForm')
    ->bind('form_login');

$app
    ->get('/logout', 'user.controller:logout')
    ->bind('logout');

$app
    ->get('/tracker/search', 'tracker.controller:search')
    ->bind('tracker_search');

$app->run();
