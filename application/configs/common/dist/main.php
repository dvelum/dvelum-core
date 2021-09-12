<?php
$docRoot = DVELUM_ROOT;
$wwwPath = DVELUM_WWW_PATH;
$wwwRoot = '/';
$language = 'en';
return array(
    'docRoot' => $docRoot,
    'wwwPath' => $wwwPath,
    'wwwRoot' => $wwwRoot,
    /*
     * Development mode
     * 0 - production
     * 1 - development
     * 2 - test (development mode + test DB)
     * 3 - installation mode
     */
    'development' => 1,
    /*
     * Templates directory
     */
    'templates' => $docRoot . '/application/templates/',
    /*
     * Url paths delimiter  "_" , "-" or "/"
     */
    'urlDelimiter' => '/',
    'urlExtension' => '',
    /*
     * System language
     * Please note. Changing the language will switch ORM storage settings.
     */
    'language' => $language,
    'timezone' => 'Europe/Moscow',

    'jsPath' => $wwwPath . 'js/',
    'jsCacheUrl' => 'js/cache/',
    'jsCachePath' => $wwwPath . 'js/cache/',

    'cssPath' => $wwwPath . 'css/',
    'cssCacheUrl' => 'css/cache/',
    'cssCachePath' => $wwwPath . 'css/cache/',

    /*
     * Main directory for config files
     */
    'configs' => '', // configs path $docRoot . '/config/',
    /**
     * Frontend controllers directories
     */
    'frontend_controllers_dirs' => ['Dvelum/App/Frontend','App/Frontend', 'Frontend'],
    /**
     * Local controllers path
     */
    'local_controllers' => './application/classes/',
    /*
     * Dictionary directory
     */
    'dictionary_folder' => 'dictionary/',
    /*
     * Temporary files directory
     */
    'tmp' => $docRoot . '/temp/',
    /*
    * Use memcached
    */
    'use_cache' => false,
    /*
     * Stop the site with message "Essential maintenance in progress. Please check back later."
     */
    'maintenance' => false,
    /*
     * Show debug panel
     */
    'debug_panel' => false,
    /*
     * www root
     */
    'wwwroot' => $wwwRoot,
    'wwwpath' => $wwwPath,
    /*
     * Frontend modules config file
     */
    'frontend_modules' => 'modules_frontend.php',
    /**
     * Relative path to DB configs
     */
    'db_config_path' => 'db/',
    /*
     * Directories for storing data base connection settings as per the system mode
     */
    'db_configs' => array(
        /* key as development mode code */
        0 => array(
            'title' => 'PRODUCTION',
            'dir' => './application/configs/prod/db/'
        ),
        1 => array(
            'title' => 'DEVELOPMENT',
            'dir' => './application/configs/dev/db/'
        ),
        2 => array(
            'title' => 'TEST',
            'dir' =>  './application/configs/test/db/'
        )
    ),
    /*
     * Application class
     */
    'application' => \Dvelum\App\Application\WebService::class,
    /*
     * Vendor library path
     */
    'vendor_lib'=> $docRoot . '/vendor/',
    /*
     * Extensions configuration
     */
    'extensions' => [
        'path' => './extensions/'
    ]
);
