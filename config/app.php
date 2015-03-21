<?php

define('CONFIG_PROJECT_ROOT', __DIR__ . '/..');
define('CONFIG_PROJECT_ENV', 'dev');

$parameters = require __DIR__ . '/parameters.php';

return [
    'project_root' => CONFIG_PROJECT_ROOT,
    'assets' => [
        'source_paths' => [
            CONFIG_PROJECT_ROOT . '/src/Front/View/Home/js',
            CONFIG_PROJECT_ROOT . '/src/Front/View/Home/img'
        ],
        'destination_path' => CONFIG_PROJECT_ROOT . '/web'
    ],
    'twig' => [
        'twig.options' => [
            'cache' => __DIR__ . '/../var/cache/twig'
        ],
        'paths' => [
            'home' => [
                __DIR__ . '/../src/Front/View/Home'
            ],
            'security' => [
                __DIR__ . '/../src/Front/View/Security'
            ],
            'user' => [
                __DIR__ . '/../src/Front/View/User'
            ],
            'form' => [
                __DIR__ . '/../src/Front/View/Form'
            ],
            'tracker' => [
                __DIR__  . '/../src/Front/View/Tracker'
            ],
            'transmission' => [
                __DIR__  . '/../src/Front/View/Transmission'
            ]
        ]
    ],
    'monolog' => [
        'monolog.logfile' => __DIR__ . '/../var/log/' . CONFIG_PROJECT_ENV . '.log'
    ],
    'translator' => [
        'translator.domains' => []
    ],
    'doctrine' => [
        'dbal' => [
            'db.options' => [
                'driver' => $parameters['doctrine_driver'],
                'dbname' => $parameters['doctrine_dbname'],
                'host' => $parameters['doctrine_host'],
                'user' => $parameters['doctrine_user'],
                'password' => $parameters['doctrine_password']
            ]
        ],
        'orm' => [
            'paths' => [
                __DIR__ . '/../src/User/Entity'
            ],
            'cache_dir' => __DIR__ . '/../var/cache/doctrine/cache',
            'proxy_dir' => __DIR__ . '/../var/cache/doctrine/proxy',
        ]
    ],
    'security' => [
        'encoder' => [
            'password' => $parameters['security_encoder_password'],
            'salt' => $parameters['security_encoder_salt']
        ]
    ],
    'tracker' => [
        'base_url' => 'https://api.t411.io',
        'client' => [
            'torrent_files_path' => $parameters['torrent_files_path']
        ]
    ],
    'transmission' => [
        'login' => $parameters['transmission_login'],
        'password' => $parameters['transmission_password'],
        'host' => $parameters['transmission_host'],
        'port' => $parameters['transmission_port'],
        'rpc_uri' => $parameters['transmission_rpc_uri'],
    ]
];
