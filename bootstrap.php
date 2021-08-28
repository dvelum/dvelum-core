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


$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
$creator = new \Nyholm\Psr7Server\ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);
$serverRequest = $creator->fromGlobals();
$response = $psr17Factory->createResponse(200);

/**
 * @var \Psr\Http\Message\ResponseInterface $resp
 */
$resp = $app->run($serverRequest , $response);
/*
 * Print debug information (development mode)
 */
if($config['development'] && $config->get('debug_panel') && (empty($serverRequest->getHeader('HTTP_X_REQUESTED_WITH')[0]) ||  $serverRequest->getHeader('HTTP_X_REQUESTED_WITH')[0]!== 'XMLHttpRequest'))
{
    $configStorage = $container->get(\Dvelum\Config\Storage\StorageInterface::class);
    $debugCfg = $configStorage->get('debug_panel.php');
    $debug = new \Dvelum\Debug();
    $debug->setCacheCores($container->get(\Dvelum\App\Cache\Manager::class)->getRegistered());
    $debug->setScriptStartTime($scriptStart);
    $debug->setLoadedClasses($container->get(\Dvelum\Autoload::class)->getLoadedClasses());
    $debug->setLoadedConfigs($configStorage->getDebugInfo());
    $resp->getBody()->write($debug->getStats($debugCfg->get('options')));
}

(new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($resp);
/*
 * Clean the buffer and send response
 */
echo ob_get_clean();