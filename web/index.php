<?php

use Martial\Warez\Application\Bootstrap;
use Silex\Application;

require_once __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/app.php';
$app = new Application();
$bootstrap = new Bootstrap($app, $config);

$bootstrap->registerControllers([
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
            $app['settings.tracker'],
            $app['transmission.manager']
        ]
    ],
    'transmission.controller' => [
        'class' => '\Martial\Warez\Front\Controller\TransmissionController',
        'dependencies' => [
            $app['transmission.manager']
        ]
    ],
    'settings.controller' => [
        'class' => '\Martial\Warez\Front\Controller\SettingsController',
        'dependencies' => [
            $app['user.service'],
            $app['settings.freebox']
        ]
    ]
]);

$app
    ->get('/', 'home.controller:index')
    ->bind('homepage');

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
    ->get('/settings', 'settings.controller:index')
    ->bind('settings');

$app
    ->get('/settings/freebox', 'settings.controller:displayFreeboxSettings')
    ->bind('settings_freebox');

$app
    ->post('/settings/freebox', 'settings.controller:updateFreeboxSettings')
    ->bind('settings_freebox_update');

$app
    ->get('/tracker/search', 'tracker.controller:search')
    ->bind('tracker_search');

$app
    ->get('/tracker/download/{torrentId}', 'tracker.controller:download')
    ->bind('tracker_download');

$app
    ->get('/transmission/torrents', 'transmission.controller:torrentList')
    ->bind('transmission_torrents');

$app
    ->get('/transmission/torrent/{torrentId}', 'transmission.controller:torrentData')
    ->bind('transmission_torrent_data');

$app->run();
