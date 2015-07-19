<?php

use Martial\Warez\Application\Bootstrap;
use Martial\Warez\Upload\UploadUrlResolver;
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
            $app['security.cookie.tokenizer']
        ]
    ],
    'security.controller' => [
        'class' => '\Martial\Warez\Front\Controller\SecurityController'
    ],
    'tracker.controller' => [
        'class' => '\Martial\Warez\Front\Controller\TrackerController',
        'dependencies' => [
            $app['t411.api.client'],
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
            $app['settings.freebox'],
            $app['settings.tracker']
        ]
    ],
    'freebox.controller' => [
        'class' => '\Martial\Warez\Front\Controller\FreeboxController',
        'dependencies' => [
            $app['upload.freebox.manager']
        ],
    ],
    'upload.controller' => [
        'class' => '\Martial\Warez\Front\Controller\UploadController',
        'calls' => [
            'setDownloadDir' => $config['download_dir'],
            'setArchiveDir' => $config['upload']['archive_path']
        ]
    ],
    'file_browser.controller' => [
        'class' => '\Martial\Warez\Front\Controller\FileBrowserController',
        'dependencies' => [
            $app['filesystem.file_browser']
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
    ->get('/settings/tracker', 'settings.controller:displayTrackerSettings')
    ->bind('settings_tracker');

$app
    ->post('/settings/tracker', 'settings.controller:updateTrackerSettings')
    ->bind('settings_tracker_update');

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

$app
    ->post('/freebox/ask-permission', 'freebox.controller:askUserPermission')
    ->bind('freebox_ask_permission');

$app
    ->get('/freebox/authorization-status/{trackId}', 'freebox.controller:getAuthorizationStatus')
    ->bind('freebox_authorization_status');

$app
    ->post('/freebox/open-session', 'freebox.controller:openSession')
    ->bind('freebox_open_session');

$app
    ->post('/freebox/upload/{filename}', 'freebox.controller:uploadFile')
    ->bind('freebox_upload_file');

$app
    ->get(UploadUrlResolver::UPLOAD_URI, 'upload.controller:upload')
    ->bind('upload_file');

$app
    ->get('/file-browser', 'file_browser.controller:browse')
    ->bind('file_browser');

$app->run();
