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

namespace  Dvelum\Db;

use Dvelum\Config;
use Dvelum\Config\ConfigInterface;
use Dvelum\Application;
use Exception;

class Manager implements ManagerInterface
{
    /**
     * @var array
     */
    protected $dbConnections = [];
    /**
     * @var array
     */
    protected $dbConfigs = [];
    /**
     * @var callable $connectionErrorHandler
     */
    protected $connectionErrorHandler;

    /**
     * @var ConfigInterface
     */
    protected $appConfig;

    /**
     * @param ConfigInterface $appConfig - Application config (main)
     */
    public function __construct(ConfigInterface $appConfig)
    {
        $this->appConfig = $appConfig;
    }

    /**
     * Get Database connection
     * @param string $name
     * @param null|string $workMode
     * @param null|string $shard
     * @return Adapter
     */
    public function getDbConnection(string $name, ?string $workMode = null, ?string $shard = null) : Adapter
    {
        if(empty($workMode)){
            $workMode = $this->appConfig->get('development');
        }

        if(empty($shard)){
            $shardKey = '1';
        }else{
            $shardKey = $shard;
        }

        if(!isset($this->dbConnections[$workMode][$name][$shardKey]))
        {
            $cfg = $this->getDbConfig($name);

            $cfg->set('driver', $cfg->get('adapter'));
            /*
             * Enable Db profiler for development mode Attention! Db Profiler causes
             * memory leaks at background tasks. (Dev mode)
             */
            if($this->appConfig->get('development') && $this->appConfig->offsetExists('use_db_profiler') && $this->appConfig->get('use_db_profiler')){
                $cfg->set('profiler' , true);
            }

            if(!empty($shard)) {
               throw new \Exception('Orm Db manager is not installed');
            }

            $db = $this->initConnection($cfg->__toArray());
            $this->dbConnections[$workMode][$name][$shardKey] = $db;
        }
        return $this->dbConnections[$workMode][$name][$shardKey];
    }

    /**
     * @param array $cfg
     * @return Adapter
     * @throws \Exception
     */
    public function initConnection(array $cfg) : Adapter
    {
        $db = new Adapter($cfg);
        $isDevMode = $this->appConfig->get('development');

        $initFunction = function(\Dvelum\Db\Adapter\Event $e) use ($db, $isDevMode, $cfg){
            if($isDevMode){
                $profiler = $db->getProfiler();
                if(!empty($profiler)){
                    \Dvelum\Debug::addDbProfiler($profiler);
                }
            }

            /*
             * Set transaction isolation level
             */
            if(isset($cfg['transactionIsolationLevel'])){
                $level = $cfg['transactionIsolationLevel'];
                if(!empty($level) && $level!=='default'){
                    $db->query('SET TRANSACTION ISOLATION LEVEL '.$level);
                }
            }
        };

        $db->on(Adapter::EVENT_INIT , $initFunction);
        if(is_callable($this->connectionErrorHandler)){
            $db->on(Adapter::EVENT_CONNECTION_ERROR, $this->connectionErrorHandler);
        }
        return $db;
    }
    /**
     * Get Db Connection config
     * @param string $name
     * @param string|null $workMode
     * @throws \Exception
     * @phpstan-return array<string,string|int>
     * @return array{host:string,username:string,password:string,dbname:string,driver:string,adapter:string,transactionIsolationLevel:string,port:int,prefix:string,charset:string}
     */
    public function getDbConfig(string $name, ?string $workMode = null) : array
    {
        if(empty($workMode)){
            $workMode = $this->appConfig->get('development');
        }

        if($workMode === Application::MODE_INSTALL){
            $workMode = Application::MODE_DEVELOPMENT;
        }

        if(!isset($this->dbConfigs[$workMode][$name]))
        {
            $dbConfigPaths = $this->appConfig->get('db_configs');

            if(!isset($dbConfigPaths[$workMode]))
                throw new Exception('Invalid application work mode ' . $workMode);

            $configPath = $dbConfigPaths[$workMode]['dir'].$name.'.php';
            $configData = include $configPath;
            $config = Config\Factory::create($configData, $configPath);
            $this->dbConfigs[$workMode][$name] = $config;
        }

        return $this->dbConfigs[$workMode][$name]->__toArray();
    }

    /**
     * Set connection error handler
     * @param callable $handler
     * @return void
     */
    public function setConnectionErrorHandler(callable $handler) : void
    {
        $this->connectionErrorHandler = $handler;
    }
}