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

namespace Dvelum\App\Console;

use Dvelum\App;
use Dvelum\Config;
use Dvelum\Log\LogInterface;
use Dvelum\App\Router;
use Dvelum\Request;
use Dvelum\Response\ResponseInterface;
use Psr\Container\ContainerInterface;

class Controller extends App\Controller implements Router\RouterInterface
{
    /**
     * Logs adapter
     * @var LogInterface|false
     */
    protected $log = false;

    /**
     * Launcher configuration
     * @var Config\ConfigInterface<string,mixed>
     */
    protected Config\ConfigInterface $consoleConfig;
    /**
     * Action routes
     * @var array<string,array> $actions
     */
    protected array $actions;

    /**
     * Controller constructor.
     * @param Request $request
     * @param ResponseInterface $response
     * @throws \Exception
     */
    public function __construct(Request $request, ResponseInterface $response, ContainerInterface $container)
    {
        if (!defined('DVELUM_CONSOLE')) {
            $response->redirect('/');
            exit;
        }

        parent::__construct($request, $response, $container);

        $storage = $container->get(Config\Storage\StorageInterface::class);
        $this->consoleConfig = $storage->get('console.php');
        // Prepare action routes
        $data = $storage->get('console_actions.php')->__toArray();
        foreach ($data as $action => $config) {
            $this->actions[strtolower($action)] = $config;
        }
    }


    /**
     * Run action
     * @param Request $request
     * @param ResponseInterface $response
     */
    public function route(Request $request, ResponseInterface $response): ResponseInterface
    {
        $this->request = $request;
        $this->response = $response;
        $this->indexAction();
        return $this->response;
    }

    /**
     * @return void
     */
    public function indexAction(): void
    {
        $action = strtolower((string)$this->request->getPart(0));

        if (empty($action) || !isset($this->actions[$action])) {
            $this->response->put('Undefined Action');
            return;
        }

        $actionConfig = $this->actions[$action];
        $adapterCls = $actionConfig['adapter'];

        if (!class_exists($adapterCls)) {
            trigger_error('Undefined Action Adapter ' . $adapterCls);
        }

        $adapter = new $adapterCls();

        if (!$adapter instanceof \Dvelum\App\Console\ActionInterface) {
            trigger_error($adapterCls . ' is not instance of ActionInterface');
        }

        $params = $this->request->getPathParts(1);
        $config = [];

        if (isset($actionConfig['config'])) {
            $config = $actionConfig['config'];
        }

        $adapter->init($this->container, $this->appConfig, $params, $config);
        $result = $adapter->run();

        echo '[' . $action . ' : ' . $adapter->getInfo() . ']' . PHP_EOL;

        if ($result) {
            exit(0);
        }

        exit(1);
    }

    /**
     * Find url
     * @param string $module
     * @return string
     */
    public function findUrl(string $module): string
    {
        return '';
    }
}
