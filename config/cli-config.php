<?php

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/app.php';
$app = new Silex\Application();
\Martial\Warez\Application\Bootstrap::createApplication($app, $config);

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($app['doctrine.entity_manager']);
