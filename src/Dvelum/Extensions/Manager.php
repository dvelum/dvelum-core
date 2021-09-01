<?php

/**
 * DVelum project https://github.com/dvelum/dvelum-core , https://github.com/dvelum
 *
 *  MIT License
 *
 *  Copyright (C) 2011-2020.  Kirill Yegorov
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
 */

declare(strict_types=1);

namespace Dvelum\Extensions;

use Dvelum\Config;
use Dvelum\Config\ConfigInterface;
use Dvelum\Config\Storage\StorageInterface;
use Dvelum\Config\Storage\StorageInterface as ConfigStorageInterface;
use Dvelum\DependencyContainer;
use Dvelum\Orm;
use Dvelum\Autoload;
use Dvelum\File;
use Dvelum\Lang;
use Dvelum\Template\Storage;
use Exception;
use Psr\Container\ContainerInterface;

/**
 * Class Manager
 * @package Dvelum\Extensions
 * @copyright Kirill Yegorov 2020
 */
class Manager
{
    /**
     * @var ConfigInterface<string,mixed>
     */
    protected ConfigInterface $appConfig;
    /**
     * @var array<string,mixed>
     */
    protected $extensionsConfig;
    /**
     * @var ConfigInterface<string,mixed>
     */
    protected ConfigInterface $config;
    /**
     * @var Autoload
     */
    protected Autoload $autoloader;
    /**
     * Loaded modules index
     * @var array<string,array>
     */
    protected array $loadedExtensions = [];

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $di;

    /**
     * Manager constructor.
     * @param ConfigInterface<string,mixed> $appConfig
     * @param ContainerInterface $di
     * @throws Exception
     */
    public function __construct(ConfigInterface $appConfig, ContainerInterface $di)
    {
        $configStorage = $di->get(ConfigStorageInterface::class);
        $this->config = $configStorage->get('extensions.php');
        $this->autoloader = $di->get(Autoload::class);
        $this->appConfig = $appConfig;
        $this->extensionsConfig = $this->appConfig->get('extensions');
        $this->di = $di;
    }

    /**
     * @param string $moduleId
     * @return bool
     */
    public function extensionRegistered(string $moduleId): bool
    {
        return $this->config->offsetExists($moduleId);
    }

    /**
     * Add extension to registry
     * @param string $extensionId
     * @param array<string,mixed> $config
     * @return bool
     */
    public function add(string $extensionId, array $config): bool
    {
        $this->config->set($extensionId, $config);
        return $this->di->get(ConfigStorageInterface::class)->save($this->config);
    }

    /**
     * Load external modules configuration
     * @return void
     */
    public function loadExtensions(): void
    {
        /**
         * @var ConfigStorageInterface $configStorage
         */
        $configStorage = $this->di->get(ConfigStorageInterface::class);
        $modules = $this->config->__toArray();

        if (empty($modules)) {
            return;
        }

        $autoLoadPaths = [];
        $autoLoadPathsPsr4 = [];
        $configPaths = [];

        $dependencyPaths = [];

        $extensionsDir = $this->extensionsConfig['path'];

        foreach ($modules as $index => $config) {
            if (!$config['enabled'] || isset($this->loadedExtensions[$index]['loaded'])) {
                continue;
            }

            $path = $extensionsDir . File::fillEndSep($config['dir']);

            if (!empty($config['paths']['src'])) {
                $autoLoadPaths[] = $path . $config['paths']['src'];
            }

            if (!empty($config['paths']['configs'])) {
                $configPaths[] = $path . $config['paths']['configs'] . '/';
            }

            if (!empty($config['paths']['dependency'])) {
                $dependencyPaths[] = $path . $config['paths']['dependency'];
            }

            /*
             * @todo implement extension locales and templates

            if (!empty($modCfg['autoloader-psr-4'])) {
                foreach ($modCfg['autoloader-psr-4'] as $ns =>$classPath) {
                    $autoLoadPathsPsr4[$ns] = str_replace('./', $path, $classPath);
                }
            }

            */
            $this->loadedExtensions[$index]['load'] = true;
        }

        // Add autoloader paths
        if (!empty($autoLoadPaths)) {
            $autoloaderConfig = $configStorage->get('autoloader.php');
            $autoloaderCfg = $autoloaderConfig->__toArray();
            $newChain = $autoloaderCfg['priority'];

            foreach ($autoLoadPaths as $path) {
                $newChain[] = $path;
            }
            $currentAutoloadPaths = $this->autoloader->getRegisteredPaths();
            foreach ($currentAutoloadPaths as $path) {
                if (!in_array($path, $newChain, true)) {
                    $newChain[] = $path;
                }
            }

            $autoloaderCfg['psr-4'] = array_merge($autoLoadPathsPsr4, $autoloaderCfg['psr-4']);
            $autoloaderCfg['paths'] = $newChain;

            // update autoloader paths
            $this->autoloader->setConfig(['paths' => $autoloaderCfg['paths'], 'psr-4' => $autoloaderCfg['psr-4']]);
            // update main configuration
            $autoloaderConfig->setData($autoloaderCfg);
        }
        // Add Config paths
        if (!empty($configPaths)) {
            $writePath = $configStorage->getWrite();
            $applyPath = $configStorage->getApplyTo();

            $paths = $configStorage->getPaths();
            $resultPaths = [];

            foreach ($paths as $path) {
                if ($path !== $writePath && $path !== $applyPath) {
                    $resultPaths[] = $path;
                }
            }
            foreach ($configPaths as $path) {
                \array_unshift($resultPaths, $path);
            }

            \array_unshift($resultPaths, $applyPath);
            $resultPaths[] = $writePath;
            $configStorage->replacePaths($resultPaths);
        }
        // register dependencies
        if (!empty($dependencyPaths)) {
            foreach ($dependencyPaths as $file) {
                if ($this->di instanceof DependencyContainer || method_exists($this->di, 'bindArray')) {
                    $this->di->bindArray(include $file);
                }
            }
        }
    }

    /**
     * Initialize core and service dependent extensions
     */
    public function initExtensions(): void
    {
        $modules = $this->config->__toArray();

        if (empty($modules)) {
            return;
        }

        $templatesPaths = [];
        $langPaths = [];

        $extensionsDir = $this->extensionsConfig['path'];

        foreach ($modules as $index => $config) {
            if (!$config['enabled'] || isset($this->loadedExtensions[$index]['init'])) {
                continue;
            }

            $path = $extensionsDir . File::fillEndSep($config['dir']);


            if (!empty($config['paths']['locales'])) {
                $langPaths[] = $path . $config['paths']['locales'] . '/';
            }

            if (!empty($config['paths']['templates'])) {
                $templatesPaths[] = $path . $config['paths']['templates'] . '/';
            }

            $this->loadedExtensions[$index]['init'] = true;
        }

        // Add localization paths
        if (!empty($langPaths)) {
            /**
             * @var StorageInterface $langStorage
             */
            $langStorage = $this->di->get(Lang::class)->getStorage();
            foreach ($langPaths as $path) {
                $langStorage->addPath($path);
            }
        }

        // Add Templates paths
        if (!empty($templatesPaths)) {
            $templateStorage = $this->di->get(Storage::class);
            $paths = $templateStorage->getPaths();
            $mainPath = array_shift($paths);
            // main path
            $pathsResult = [];
            $pathsResult[] = $mainPath;
            $pathsResult = array_merge($pathsResult, $templatesPaths, $paths);
            $templateStorage->setPaths($pathsResult);
        }
    }
}
