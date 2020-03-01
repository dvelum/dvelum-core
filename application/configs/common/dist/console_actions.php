<?php
/*
 *  Console actions
 */
return [
    // Create Model classes
    'generateClassMap'=>[
        'adapter' => '\\Dvelum\\App\\Console\\Generator\\ClassMap'
    ],
    // clear static files cache (js,css)
    'clearStatic'=>[
        'adapter' => '\\Dvelum\\App\\Console\\Clear\\StaticCache'
    ],
    // register extension
    'extension-add' =>[
        'adapter' => '\\Dvelum\\App\\Console\\Extension\\Add'
    ]
];
