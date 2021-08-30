<?php

return [
    // PSR-0 autoload paths
    'paths' => [
        './application/classes',
        './src',
    ],
    // paths priority (cannot be overridden by external modules)
    'priority'=>[
        './application/classes',
        './src',
    ],
    // Use class maps
    'useMap' => true,
    // Use class map (Reduce IO load during autoload)
    // Class map file path (string / false)
    'map' => 'classmap.php',
    // PSR-4 autoload paths
    'psr-4' =>[
        'Psr\\Log'=>'./vendor/psr/log/Psr/Log',

        'Dvelum\\Db' => './vendor/dvelum/db/src',
        'Dvelum\\Cache' => './vendor/dvelum/cache/src',

        'Laminas\\Stdlib' => './vendor/laminas/laminas-stdlib/src',
        'Laminas\\Db' => './vendor/laminas/laminas-db/src',
        'Laminas\\Mail' => './vendor/laminas/laminas-mail/src',
        'Laminas\\Mime' => './vendor/laminas/laminas-mime/src',
        'Laminas\\Validator' => './vendor/laminas/laminas-validator/src',
        'Laminas\\Loader' => './vendor/laminas/laminas-loader/src',

        'MatthiasMullie\\Minify' => './vendor/matthiasmullie/minify/src',
        'MatthiasMullie\\PathConverter' => './vendor/matthiasmullie/path-converter/src',

    ],
    // Paths to be excluded from class map
    'noMap' =>[

    ]
];