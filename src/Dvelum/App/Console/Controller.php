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

namespace Dvelum\App\Console;

use Dvelum\App;
use Dvelum\Config;
use Dvelum\Log\LogInterface;
use Dvelum\Orm\Model;
use Dvelum\App\Router;
use Dvelum\Request;
use Dvelum\Response;
use Psr\Log\LogLevel;

class Controller extends App\Controller implements Router\RouterInterface
{
    /**
     * Logs adapter
     * @var LogInterface
     */
    protected $log = false;
    /**
     * Cron User
     * @var App\Session\User
     */
    protected $user;

    /**
     * Launcher configuration
     * @var array
     */
    protected $consoleConfig;
    /**
     * Action routes
     * @var array $actions
     */
    protected $actions;

    public function __construct(Request $request, Response $response)
    {
        if (!defined('DVELUM_CONSOLE')) {
            $this->response->redirect('/');
            exit;
        }

        parent::__construct($request, $response);

        $this->consoleConfig = Config::storage()->get('console.php');
        // Prepare action routes
        $data = Config::storage()->get('console_actions.php')->__toArray();
        foreach ($data as $action => $config){
            $this->actions[strtolower($action)] = $config;
        }
        $log = $this->consoleConfig->get('log');

        if ($log['enabled']) {
            switch ($log['type']) {
                case 'file' :
                    $this->log = new \Dvelum\Log\File($log['logFile']);
                    break;
            }
        }
        $this->authorize();
    }

    /**
     * Authorize as system user
     */
    protected function authorize()
    {
        $userId = $this->consoleConfig->get('user_id');
        if ($userId && Model::factory('User')->query()->filters(['id' => $userId])->getCount()) {
            $curUser = App\Session\User::factory();
            $curUser->setId($userId);
            $curUser->setAuthorized();
            $this->user = $curUser;
        } else {
            $this->logMessage('Cron  cant\'t authorize');
        }
    }

    /**
     * Log message
     * @param string $text
     */
    protected function logMessage($text)
    {
        if ($this->log) {
            $this->log->log(LogLevel::ERROR, get_called_class() . ' :: '. $text);
        }
    }

    /**
     * Run action
     * @param Request $request
     * @param Response $response
     */
    public function route(Request $request, Response $response): void
    {
        $this->response = $response;
        $this->request = $request;
        $this->indexAction();
    }

    public function indexAction()
    {
        $action = strtolower($this->request->getPart(0));

        if (empty($action) || !isset($this->actions[$action])) {
            $this->response->put('Undefined Action');
            return;
        }

        $actionConfig = $this->actions[$action];
        $adapterCls = $actionConfig['adapter'];

        if (!class_exists($adapterCls)) {
            trigger_error('Undefined Action Adapter ' . $adapterCls);
        }

        $adapter = new $adapterCls($actionConfig);

        if (!$adapter instanceof \Dvelum\App\Console\ActionInterface) {
            trigger_error($adapterCls . ' is not instance of ActionInterface');
        }

        $params = $this->request->getPathParts(1);
        $config = [];

        if(isset($actionConfig['config'])){
            $config = $actionConfig['config'];
        }

        $adapter->init($this->appConfig, $params , $config);
        $result = $adapter->run();

        echo '[' . $action . ' : ' . $adapter->getInfo() . ']' . PHP_EOL;

        if ($result) {
            exit(0);
        } else {
            exit(1);
        }
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