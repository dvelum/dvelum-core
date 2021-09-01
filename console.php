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

$_SERVER['REQUEST_URI'] = $_SERVER['argv'][1];
$request = new \Dvelum\Request();
// Can be replaced with \Dvelum\Response\PsrResponse($psrResponse)
$response = new \Dvelum\Response\Response();


/**
 * @var \Dvelum\Response\ResponseInterface $resp
 */
$resp = $app->runConsole($request, $response);
if(!$resp->isSent()){
    $resp->send();
}