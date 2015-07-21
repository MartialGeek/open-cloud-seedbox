<?php

define('CONFIG_PROJECT_ROOT', __DIR__ . '/..');

$parameters = require __DIR__ . '/parameters.php';

return [
    'application' => [
        'env' => $parameters['app_env'],
        'host' => $parameters['app_host']
    ],
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
            'form' => [
                __DIR__ . '/../src/Front/View/Form'
            ],
            'tracker' => [
                __DIR__  . '/../src/Front/View/Tracker'
            ],
            'transmission' => [
                __DIR__  . '/../src/Front/View/Transmission'
            ],
            'settings' => [
                __DIR__ . '/../src/Front/View/Settings'
            ],
            'file_browser' => [
                __DIR__ . '/../src/Front/View/FileBrowser'
            ]
        ]
    ],
    'monolog' => [
        'monolog.logfile' => __DIR__ . '/../var/log/' . $parameters['app_env'] . '.log'
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
                __DIR__ . '/../src/User/Entity',
                __DIR__ . '/../src/Settings/Entity'
            ],
            'cache_dir' => __DIR__ . '/../var/cache/doctrine/cache',
            'proxy_dir' => __DIR__ . '/../var/cache/doctrine/proxy',
        ],
        'migrations' => [
            'migrations.directory'  => __DIR__ . '/../data/migrations',
            'migrations.name'       => 'Warez Migrations',
            'migrations.namespace'  => 'Martial\Warez\Migrations',
            'migrations.table_name' => 'warez_migrations',
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
    'upload' => [
        'adapter' => $parameters['upload_adapter'],
        'archive_path' => $parameters['upload_archive_path']
    ],
    'transmission' => [
        'login' => $parameters['transmission_login'],
        'password' => $parameters['transmission_password'],
        'host' => $parameters['transmission_host'],
        'port' => $parameters['transmission_port'],
        'rpc_uri' => $parameters['transmission_rpc_uri'],
    ],
    'download_dir' => $parameters['download_dir'],
    'message_queuing' => [
        'freebox' => [
            'connection' => [
                'host' => $parameters['message_queuing_freebox_connection_host'],
                'port' => $parameters['message_queuing_freebox_connection_port'],
                'user' => $parameters['message_queuing_freebox_connection_user'],
                'password' => $parameters['message_queuing_freebox_connection_password'],
                'vhost' => $parameters['message_queuing_freebox_connection_vhost'],
            ]
        ]
    ],
    'file_browser' => [
        'root_path' => $parameters['file_browser_root_path']
    ],
    'serializer' => [
        'cache_dir' => __DIR__ . '/../var/cache/serializer'
    ]
];
