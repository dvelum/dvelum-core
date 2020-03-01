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

declare(strict_types=1);

namespace Dvelum;

use Dvelum\{App\Cache, Config\ConfigInterface, Db, Cache\CacheInterface, Extensions\Manager};


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
     * @var boolean
     */
    protected $initialized = false;

    /**
     * @var Autoload
     */
    protected $autoloader;

    /**
     * The constructor accepts the main configuration object as an argument
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Inject Auto-loader
     * @param Autoload $autoloader
     * @return void
     */
    public function setAutoloader(Autoload $autoloader) : void
    {
        $this->autoloader = $autoloader;
    }

    /**
     * Initialize the application, configure the settings, inject dependencies
     * Adjust the settings necessary for running the system
     * @return void
     * @throws \Exception
     */
    protected function init() : void
    {
        if ($this->initialized) {
            return;
        }

        /*
         * Init extensions
         */
        $this->initExtensions();

        $config = & $this->config->dataLink();

        date_default_timezone_set($config['timezone']);

        /*
         * Init cache connection
         */
        $cache = $this->initCache();
        $this->cache = $cache;

        /*
         * Init database connection
         */
        $dbManager = $this->initDb();

        /*
         * Init templates storage
         */
        $templateStorage = View::storage();
        $templateStorage->setConfig(Config\Factory::storage()->get('template_storage.php')->__toArray());

        $request = Request::factory();
        $request->setConfig(Config\Factory::create([
            'delimiter' => $config['urlDelimiter'],
            'extension' => $config['urlExtension'],
            'wwwRoot' => $config['wwwRoot']
        ]));

        $resource = Resource::factory();
        $resource->setConfig(Config\Factory::create([
            'jsCacheUrl' => $config['jsCacheUrl'],
            'jsCachePath' => $config['jsCachePath'],
            'cssCacheUrl' => $config['cssCacheUrl'],
            'cssCachePath' => $config['cssCachePath'],
            'wwwRoot' => $config['wwwRoot'],
            'wwwPath' => $config['wwwPath'],
            'cache' => $cache
        ]));

        /*
         * Register Services
         */
        Service::register(
            Config::storage()->get('services.php'),
            Config\Factory::create([
                'appConfig' => $this->config,
                'dbManager' => $dbManager,
                'cache' => $cache
            ])
        );
        $this->initialized = true;
    }


    /**
     * Initialize Cache connections
     * @return CacheInterface | null
     */
    protected function initCache(): ? CacheInterface
    {
        if (!$this->config->get('use_cache')) {
            return null;
        }

        $cacheConfig = Config::storage()->get('cache.php')->__toArray();
        $cacheManager = new Cache\Manager();

        foreach ($cacheConfig as $name => $cfg) {
            if ($cfg['enabled']) {
                $cacheManager->connect($name, $cfg);
            }
        }

        if ($this->config->get('development')) {
            Debug::setCacheCores($cacheManager->getRegistered());
        }
        /**
         * @var CacheInterface $cache
         */
        $cache = $cacheManager->get('data');

        if(empty($cache)){
            return null;
        }

        return $cache;
    }

    /**
     * Initialize Database connection
     * @return Db\ManagerInterface
     * @throws \Exception
     */
    protected function initDb()
    {
        $dev = $this->config->get('development');
        $dbErrorHandler = function ( Db\Adapter\Event $e) use( $dev){
            $response = Response::factory();
            $request = Request::factory();
            if($request->isAjax()){
                $response->error(Lang::lang()->get('CANT_CONNECT'));
                exit();
            }else{
                $tpl = View::factory();
                $tpl->set('error_msg', ' ' . $e->getData()['message']);
                $tpl->set('development', $dev);
                echo $tpl->render('public/error.php');
                exit();
            }
        };

        $useProfiler = false;
        if($dev && $this->config->get('debug_panel')){
            $useProfiler = Config::storage()->get('debug_panel.php')->get('options')['sql'];
        }

        $this->config->set('use_db_profiler', $useProfiler);

        $managerClass = $this->config->get('db_manager');

        $conManager = new $managerClass($this->config);
        $conManager->setConnectionErrorHandler($dbErrorHandler);
        return $conManager;
    }

    /**
     * Start application
     * @return void
     */
    public function run() : void
    {
        $this->init();
    }

    /**
     * Start application in test mode
     * @return void
     */
    public function runTestMode() : void
    {
        $this->init();
    }

    /**
     * Start application in install mode
     * @return void
     */
    public function runInstallMode() : void
    {
        $this->init();
    }

    /**
     * Start console application
     * @return void
     */
    public function runConsole() : void
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
    protected function routeFrontend() : void
    {
        $request = Request::factory();
        $response = Response::factory();

        if ($this->config->get('maintenance')) {
            $tpl = View::factory();
            $tpl->set('msg', Lang::lang()->get('MAINTENANCE'));
            $response->put($tpl->render('public/maintenance.php'));
            $response->send();
            return;
        }

        /*
        $auth = new Auth($request, $this->config);
        $auth->auth();
         */

        /*
         * Start routing
        */
        $frontConfig = Config::storage()->get('frontend.php');
        $routerClass = '\\Dvelum\\App\\Router\\' . $frontConfig->get('router');

        if (!class_exists($routerClass)) {
            $routerClass = $frontConfig->get('router');
        }

        /**
         * @var \Dvelum\App\Router $router
         */
        $router = new $routerClass();
        $router->route($request, $response);

        if (!$response->isSent()) {
            $response->send();
        }
    }

    /**
     * Init additional external modules
     * defined in external_modules option
     * of main configuration file
     */
    protected function initExtensions()
    {
        $extensions = Config\Factory::storage()->get('extensions.php');
        if(empty($extensions)){
            return;
        }

        $manager = new Manager($this->config, $this->autoloader);
        $manager->loadExtensions();
    }
}