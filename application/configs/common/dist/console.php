<?php
return [
    'router' => '\\Dvelum\\App\\Router\\Console',
    'controller' => '\\Dvelum\\App\\Console\\Controller',
    'lockConfig'=>[
        'time_limit'=> 300,
        'intercept_timeout'=>300,
        'locks_dir'=> './data/locks/',
    ]
];