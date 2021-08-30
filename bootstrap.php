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
/*
 * Turning on output buffering
 */
ob_start();
/**
 * @var \Dvelum\Application $app
 */
$app = require 'bootstrap_app.php';

/**
 * @var \Psr\Container\ContainerInterface $container
 */
$container = $app->getDiContainer();
/**
 * @var array $config
 */
$config = $container->get('config.main');

$request = new \Dvelum\Request();

// Can be replaced with \Dvelum\Response\PsrResponse($psrResponse)
$response = new \Dvelum\Response\Response();

/**
 * @var \Dvelum\Response\ResponseInterface $resp
 */
$resp = $app->run($request , $response);
if(!$resp->isSent()){
    $resp->send();
}

/*
 * Clean the buffer and send response
 */
echo ob_get_clean();
$scriptStop = microtime(true);
/*
 * Print debug information (development mode)
 */
if($config['development'] && $config->get('debug_panel') && !$request->isAjax())
{
    $configStorage = $container->get(\Dvelum\Config\Storage\StorageInterface::class);
    $debugCfg = $configStorage->get('debug_panel.php');
    $debug = \Dvelum\Debug::instance();
    $debug->setCacheCores($container->get(\Dvelum\App\Cache\Manager::class)->getRegistered());
    $debug->setScriptStartTime($scriptStart);
    $debug->setScriptStopTime($scriptStop);
    $debug->setLoadedClasses($container->get(\Dvelum\Autoload::class)->getLoadedClasses());
    $debug->setLoadedConfigs($configStorage->getDebugInfo());
    echo $debug->getStats($debugCfg->get('options'));
}
