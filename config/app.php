<?php

define('CONFIG_PROJECT_ROOT', __DIR__ . '/..');

return [
    'assets' => [
        'source_paths' => [
            CONFIG_PROJECT_ROOT . '/src/Front/View/Home/js'
        ],
        'destination_path' => CONFIG_PROJECT_ROOT . '/web'
    ]
];
