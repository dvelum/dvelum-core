<?php

/**
 * DVelum project https://github.com/dvelum/dvelum-core , https://github.com/dvelum/dvelum
 *
 * MIT License
 *
 * Copyright (C) 2011-2021  Kirill Yegorov
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
declare(strict_types=1);

namespace Dvelum;

use App\Config\Storage;
use Dvelum\Cache\CacheInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Dvelum\Config\Storage\StorageInterface as ConfigStorageInterface;
use Dvelum\Extensions\Manager as ExtensionManager;
use Dvelum\Lang;


/**
 * Application - is the main class that initializes system configuration
 * settings. The system starts working with running an object of this class.
 * @author Kirill A Egorov
 */
class Application
{
    const MODE_PRODUCTION = 0;
    const MODE_DEVELOPMENT = 1;
    const MODE_TEST = 2;
    const MODE_INSTALL = 3;

    /**
     * Application config
     * @var Config\ConfigInterface
     */
    protected $config;

    /**
     * @var CacheInterface|null
     */
    protected $cache;

    /**
     * @var bool
     */
    protected $initialized = false;

    /**
     * @var Autoload
     */
    protected $autoloader;


    protected ContainerInterface $diContainer;
    protected StorageInterface $configStorage;

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
    protected function init(): void
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
     * @return void
     */
    public function run(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
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
     * @return void
     */
    public function runConsole(): void
    {
        $this->init();
        $request = Request::factory();
        $response = Response::factory();
        $config = Config::storage()->get('console.php');
        $routerClass = $config->get('router');
        $router = new $routerClass();
        $router->route($request, $response);
        if (!$response->isSent()) {
            $response->send();
        }
    }


    /**
     * Run frontend application
     * @return void
     */
    protected function routeFrontend(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
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
        $extensions = $this->diContainer->get(ConfigStorageInterface::class)->get('extensions.php');
        if (empty($extensions)) {
            return;
        }
        /**
         * @var ExtensionManager $extensionManager
         */
        $extensionManager = $this->diContainer->get(ExtensionManager::class);
        $extensionManager->loadExtensions();
        $extensionManager->initExtensions();
    }
}