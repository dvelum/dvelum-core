<?php
/*
 *  Console actions
 */
return [
    // Create Model classes
    'generateClassMap'=>[
        'adapter' => \Dvelum\App\Console\Generator\ClassMap::class
    ],
    // clear static files cache (js,css)
    'clearStatic'=>[
        'adapter' => \Dvelum\App\Console\Clear\StaticCache::class
    ],
    // register extension
    'extension-add' =>[
        'adapter' => \Dvelum\App\Console\Extension\Add::class
    ]
];
