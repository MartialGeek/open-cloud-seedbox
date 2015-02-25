<?php

use Martial\Warez\Application\Bootstrap;
use Silex\Application;

require_once __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/app.php';
$app = new Application();
Bootstrap::createApplication($app, $config, 'dev');

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
