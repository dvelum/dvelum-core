<?php

/*
 * DVelum console application
 * Return codes
 * 0 - Good
 * 1 - Empty URI
 * 2 - Wrong URI
 * 3 - Application Error
 */
if (isset($_SERVER['argc']) && $_SERVER['argc'] < 2) {
    exit(1);
}

$scriptStart = microtime(true);
define('DVELUM_CONSOLE', true);
/**
 * @var \Dvelum\Application $app
 */
$app = require 'bootstrap_app.php';


$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
$creator = new \Nyholm\Psr7Server\ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);

$serverRequest = $creator->fromArrays([
    'REQUEST_URI' => $_SERVER['argv'][1],
    'REQUEST_METHOD' => 'GET'
]);

$response = $psr17Factory->createResponse(200);

/**
 * @var Psr\Http\Message\ResponseInterface $resp
 */
$resp = $app->runConsole($serverRequest, $response);

(new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($resp);