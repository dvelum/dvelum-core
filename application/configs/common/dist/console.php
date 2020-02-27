<?php
return [
    'router' => '\\Dvelum\\App\\Router\\Console',
    'controller' => '\\Dvelum\\App\\Console\\Controller',
    'user_id'=>1,
    'lockConfig'=>[
        'time_limit'=> 300,
        'intercept_timeout'=>300,
        'locks_dir'=> './data/locks/',
    ]
];