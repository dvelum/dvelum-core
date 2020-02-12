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

/**
 * PSR-0 / PSR-4 Autoload class
 *
 * @author Kirill Egorov
 */
class Autoload
{
    protected $debug = false;
    protected $debugData = [];
    protected $classMap = [];
    protected $paths = [];
    protected $psr4Paths = [];

    protected $loaders = ['psr0', 'psr4'];

    /**
     * Set autoload config
     *
     * @param array $config
     *          Example:
     *          array(
     *          // Debug mode
     *          'debug' => boolean
     *          // Paths for autoloading
     *          'paths'=> array(...),
     *          // Class map
     *          'map' => array(...),
     *          'psr-4' => [prefix=>path]
     *          );
     */
    public function __construct(array $config)
    {
        $this->setConfig($config);
        spl_autoload_register([$this, 'load'], true, true);
    }

    /**
     * Reload configuration options
     * @param array $config
     * @return void
     */
    public function setConfig(array $config): void
    {
        if (isset($config['paths'])) {
            $this->paths = array_values($config['paths']);
        }

        if (isset($config['map']) && !empty($config['map'])) {
            $this->classMap = $config['map'];
        }

        if (isset($config['debug']) && $config['debug']) {
            $this->debug = true;
        }

        if (isset($config['psr-4']) && !empty($config['psr-4'])) {
            $this->psr4Paths = $config['psr-4'];
        }
    }

    /**
     * Register library path
     * @param string $path
     * @param boolean $prepend , optional false  - priority
     * @return void
     */
    public function registerPath($path, $prepend = false)
    {
        if ($prepend) {
            array_unshift($this->paths, $path);
        } else {
            $this->paths[] = $path;
        }
    }

    /**
     * Register library paths
     * @param array $paths
     * @return void
     */
    public function registerPaths(array $paths): void
    {
        if (empty($paths)) {
            return;
        }

        foreach ($paths as $path) {
            $this->registerPath($path);
        }
    }

    /**
     * Load class
     * @param string $class
     * @return bool
     */
    public function load(string $class): bool
    {
        /*
         * Try to load from map
         */
        if (!empty($this->classMap) && isset($this->classMap[$class])) {
            try {
                require_once $this->classMap[$class];
            } catch (\Throwable $e) {
                return false;
            }

            if ($this->debug) {
                $this->debugData[] = $class;
            }
            return true;
        }

        foreach ($this->loaders as $loader) {
            if ($this->{$loader}($class)) {
                if ($this->debug) {
                    $this->debugData[] = $class . ' -no map';
                }
                return true;
            }
        }
        return false;
    }

    /**
     * PSR-0 autoload
     * @param string $class
     * @return bool
     */
    public function psr0(string $class): bool
    {
        $file = str_replace(['_', '\\'], DIRECTORY_SEPARATOR, $class) . '.php';

        foreach ($this->paths as $path) {
            if (file_exists($path . DIRECTORY_SEPARATOR . $file)) {
                try {
                    require_once $path . DIRECTORY_SEPARATOR . $file;
                    return true;
                } catch (\Throwable $e) {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * PSR-4 autoload
     * @param string $class
     * @return bool
     */
    public function psr4(string $class): bool
    {
        foreach ($this->psr4Paths as $prefix => $path) {
            if (strpos($class, $prefix) === 0) {
                $filePath = str_replace([$prefix, '\\'], [$path, '/'], $class) . '.php';
                if (file_exists($filePath)) {
                    try {
                        require_once $filePath;
                        return true;
                    } catch (\Throwable $e) {
                        return false;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Load class map
     * @param array $data
     * @return void
     */
    public function setMap(array $data): void
    {
        $this->classMap = $data;
    }

    /**
     * Add class map
     * @param array $data
     * @return void
     */
    public function addMap(array $data): void
    {
        foreach ($data as $k => $v) {
            $this->classMap[$k] = $v;
        }
    }

    /**
     * Debug function.
     * Shows loaded class files
     * @return array
     */
    public function getLoadedClasses(): array
    {
        return $this->debugData;
    }

    /**
     * Get list of registered autoload paths
     * @return array
     */
    public function getRegisteredPaths(): array
    {
        return $this->paths;
    }
}