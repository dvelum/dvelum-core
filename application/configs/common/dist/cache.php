<?php
return [
    'adapter' => \Dvelum\Cache\Memcached::class,
    'options' => [
        'compression' => 1,
        'normalizeKeys' => 1,
        'defaultLifeTime' => 604800, // 7 days
        'keyPrefix' => 'dv_dat',
        'persistent_key' => 'dv_cache_',
        'servers' => [
            [
                'host' => 'localhost',
                'port' => 11211,
                'weight' => 1,
            ]
        ]
    ]
];