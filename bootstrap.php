<?php
/**
 * DVelum project https://github.com/dvelum/dvelum-core , https://github.com/dvelum/dvelum
 *
 * MIT License
 *
 * Copyright (C) 2011-2020  Kirill Yegorov
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 */
/*
 * Startup time
 */
$scriptStart = microtime(true);

$dvelumRoot =  str_replace('\\', '/' , __DIR__);
// should be without last slash
if ($dvelumRoot[strlen($dvelumRoot) - 1] == '/')
    $dvelumRoot = substr($dvelumRoot, 0, -1);

define('DVELUM', true);
define('DVELUM_ROOT' ,$dvelumRoot);

chdir(DVELUM_ROOT);

/*
 * Httponly cookies
 */
ini_set("session.cookie_httponly", 1);
/*
 * Turning on output buffering
 */
ob_start();

//===== loading kernel =========
/*
 * Including initial config
 */
$bootCfg = include DVELUM_ROOT . '/application/configs/common/dist/init.php';

/*
 * Register composer autoload
 */
require DVELUM_ROOT . '/vendor/autoload.php';
/*
 * Including Autoloader class
 */
require DVELUM_ROOT . '/src/Dvelum/Autoload.php';
$autoloader = new \Dvelum\Autoload($bootCfg['autoloader']);

use Dvelum\Autoload;
use \Dvelum\Config\Factory as ConfigFactory;
use Dvelum\Config\Storage\StorageInterface;
use Dvelum\DependencyContainer;

$configStorage = ConfigFactory::storage();
$configStorage->setConfig($bootCfg['config_storage']);

//==== Loading system ===========
/*
 * Reload storage options from local system
 */
$configStorage->setConfig(ConfigFactory::storage()->get('config_storage.php')->__toArray());
/*
 * Connecting main configuration file
 */
$config = ConfigFactory::storage()->get('main.php');

switch ($config->get('development')){
    // production
    case 0 :
        $configStorage->addPath('./application/configs/prod/');
        break;
    // development
    case 1 :
        $configStorage->addPath('./application/configs/dev/');
        /*
         * Disable op caching for development mode
         */
        ini_set('opcache.enable', 0);
        $configStorage->setConfig(['debug' => true]);
        break;
    // test
    case 2 :
        $configStorage->addPath('./application/configs/test/');
        break;
}
/*
 * Setting autoloader config
 */
$autoloaderCfg = $configStorage->get('autoloader.php')->__toArray();
$autoloaderCfg['debug'] = $config->get('development');

if(!isset($autoloaderCfg['useMap']))
    $autoloaderCfg['useMap'] = true;

if($autoloaderCfg['useMap'] && $autoloaderCfg['map'])
    $autoloaderCfg['map'] = require $configStorage->getPath($autoloaderCfg['map']);
else
    $autoloaderCfg['map'] = false;

$autoloader->setConfig($autoloaderCfg);

/*
 * Installation mode
 */
if($config->get('development') === 3){
    if(strpos($_SERVER['REQUEST_URI'],'install')!==false){
        $controller = new Dvelum\App\Install\Controller();
        $controller->setAutoloader($autoloader);
        $controller->run();
        exit;
    }else{
        echo 'DVelum software is not installed';
        exit;
    }
}

/*
 * Starting the application
 */
$appClass = $config->get('application');
if(!class_exists($appClass))
    throw new Exception('Application class '.$appClass.' does not exist! Check config "application" option!');


$diContainer= new DependencyContainer();
$diContainer->bind('config.main', $config);
$diContainer->bind(StorageInterface::class, $configStorage);
$diContainer->bind(Autoload::class, $autoloader);
$diContainer->bindArray($configStorage->get('dependency.php')->__toArray());


$app = new $appClass($diContainer);

$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
$creator = new \Nyholm\Psr7Server\ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);
$serverRequest = $creator->fromGlobals();
$response = $psr17Factory->createResponse(200)->withAddedHeader('Content-Type', 'application/json; charset=utf-8');

/**
 * @var \Psr\Http\Message\ResponseInterface $resp
 */
$resp = $app->run($serverRequest , $response);

/*
 * Print debug information (development mode)
 */
if($config['development'] && $config->get('debug_panel') && !\Dvelum\Request::factory()->isAjax())
{
    $debugCfg = $configStorage->get('debug_panel.php');
    $debug = new \Dvelum\Debug();
    $debug->setCacheCores($app->getDiConteiner()->get(\Dvelum\App\Cache\Manager::class)->getRegistered());
    $debug->setScriptStartTime($scriptStart);
    $debug->setLoadedClasses($autoloader->getLoadedClasses());
    $debug->setLoadedConfigs($configStorage->getDebugInfo());
    $resp->getBody()->write($debug->getStats($debugCfg->get('options')));
}

(new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($resp);
/*
 * Clean the buffer and send response
 */
echo ob_get_clean();