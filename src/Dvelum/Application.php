<?php

/*
 *
 * DVelum project https://github.com/dvelum/
 *
 * MIT License
 *
 *  Copyright (C) 2011-2021  Kirill Yegorov https://github.com/dvelum/dvelum-core
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *  SOFTWARE.
 *
 */

declare(strict_types=1);

namespace Dvelum;

use Dvelum\Response\ResponseInterface;
use Dvelum\Config\Storage\StorageInterface as ConfigStorageInterface;
use Psr\Container\ContainerInterface;

/**
 * Application - is the main class that initializes system configuration
 * settings. The system starts working with running an object of this class.
 * @author Kirill A Egorov
 */
class Application
{
    public const MODE_PRODUCTION = 0;
    public const MODE_DEVELOPMENT = 1;
    public const MODE_TEST = 2;
    public const MODE_INSTALL = 3;

    /**
     * Application config
     * @var Config\ConfigInterface<string,mixed>
     */
    protected Config\ConfigInterface $config;

    /**
     * @var \Dvelum\Cache\CacheInterface|null
     */
    protected $cache;

    /**
     * @var bool
     */
    protected $initialized = false;

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $diContainer;

    public function __construct(ContainerInterface $container)
    {
        $this->diContainer = $container;
        $this->config = $container->get('config.main');
    }

    public function getDiContainer(): ContainerInterface
    {
        return $this->diContainer;
    }

    /**
     * Initialize the application, configure the settings, inject dependencies
     * Adjust the settings necessary for running the system
     * @return void
     * @throws \Exception
     */
    public function init(): void
    {
        if ($this->initialized) {
            return;
        }

        $config = $this->diContainer->get('config.main');
        date_default_timezone_set($config->get('timezone'));

        /*
         * Load extensions
         */
        $this->loadExtensions();
        $this->initialized = true;
    }

    /**
     * Start application
     * @return ResponseInterface
     */
    public function run(Request $request, ResponseInterface $response): ResponseInterface
    {
        $this->init();
        return $response;
    }

    /**
     * Start application in test mode
     * @return void
     */
    public function runTestMode(): void
    {
        $this->init();
    }

    /**
     * Start application in install mode
     * @return void
     */
    public function runInstallMode(): void
    {
        $this->init();
    }

    /**
     * Start console application
     * @return ResponseInterface
     */
    public function runConsole(Request $request, ResponseInterface $response): ResponseInterface
    {
        $this->init();
        return $this->routeConsole($request, $response);
    }

    /**
     * @param Request $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    protected function routeConsole(Request $request, ResponseInterface $response): ResponseInterface
    {
        $storage = $this->diContainer->get(ConfigStorageInterface::class);
        $config = $storage->get('console.php');
        $routerClass = $config->get('router');
        $router = new $routerClass($this->diContainer);
        return $router->route($request, $response);
    }


    /**
     * Run frontend application
     * @return ResponseInterface
     */
    protected function routeFrontend(Request $request, ResponseInterface $response): ResponseInterface
    {
        $storage = $this->diContainer->get(ConfigStorageInterface::class);
        /*
         * Start routing
        */
        $frontConfig = $storage->get('frontend.php');
        $routerClass = $frontConfig->get('router');

        if (!class_exists($routerClass)) {
            $routerClass = $frontConfig->get('router');
        }

        /**
         * @var \Dvelum\App\Router\RouterInterface $router
         */
        $router = new $routerClass($this->diContainer);
        return $router->route($request, $response);
    }

    /**
     * Load additional core extensions
     * @return void
     */
    protected function loadExtensions(): void
    {
        $extensionsCount = $this->diContainer->get(ConfigStorageInterface::class)->get('extensions.php')->getCount();
        if (empty($extensionsCount)) {
            return;
        }
        /**
         * @var \Dvelum\Extensions\Manager $extensionManager
         */
        $extensionManager = $this->diContainer->get(\Dvelum\Extensions\Manager::class);
        $extensionManager->loadExtensions();
        $extensionManager->initExtensions();
    }
}
