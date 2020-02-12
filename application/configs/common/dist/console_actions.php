<?php
/*
 *  Console actions
 */
return [
    // Create Model classes
    'generateClassMap'=>[
        'adapter' => '\\Dvelum\\App\\Console\\Generator\\ClassMap'
    ],
    'clearStatic'=>[
        'adapter' => '\\Dvelum\\App\\Console\\Clear\\StaticCache'
    ],
];
