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

namespace Dvelum\App;

use Dvelum\Autoload;
use Dvelum\Config\ConfigInterface;
use Dvelum\Config;
use Dvelum\Externals\Manager;
use Dvelum\File;
use Dvelum\Utils;

/**
 * Classmap builder
 */
class Classmap
{
    /**
     * @var array<int|string,string>
     */
    protected array $map = [];
    /**
     * @var ConfigInterface<int|string,mixed> $appConfig
     */
    protected ConfigInterface $appConfig;
    /**
     * @var array<int|string,mixed>
     */
    protected array $autoloaderCfg = [];

    /**
     * Classmap constructor.
     * @param ConfigInterface<int|string,mixed> $appConfig
     */
    public function __construct(ConfigInterface $appConfig)
    {
        $this->appConfig = $appConfig;
        $this->autoloaderCfg = Config::storage()->get('autoloader.php')->__toArray();
    }

    /**
     * Load classmap from file
     * @return void
     */
    public function load(): void
    {
        $map = Config::storage()->get($this->autoloaderCfg['map']);
        if (!empty($map)) {
            $this->map = $map->__toArray();
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function update(): void
    {
        $path = $this->autoloaderCfg['paths'];

        $this->map = [];
        foreach ($path as $v) {
            $v = File::fillEndSep($v);
            if (is_dir($v)) {
                $this->findClasses($v, $v);
            }
        }

        $psr4 = $this->autoloaderCfg['psr-4'];
        foreach ($psr4 as $baseSpace => $path) {
            $v = File::fillEndSep($path);
            if (is_dir($v)) {
                $this->findPsr4Classes($v, $v, $baseSpace);
            }
        }
        ksort($this->map);
    }

    /**
     * Find PHP Classes
     * @param string $path
     * @param string $exceptPath
     * @throws \Exception
     * c
     */
    protected function findClasses(string $path, string $exceptPath): void
    {
        $path = File::fillEndSep($path);
        $items = File::scanFiles($path, ['.php'], false);

        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            if (File::getExt($item) === '.php') {
                if (!empty($this->autoloaderCfg['noMap'])) {
                    $found = false;
                    foreach ($this->autoloaderCfg['noMap'] as $excludePath) {
                        if (strpos($item, $excludePath) !== false) {
                            $found = true;
                            break;
                        }
                    }
                    if ($found) {
                        continue;
                    }
                }

                $parts = explode('/', str_replace($exceptPath, '', substr($item, 0, -4)));
                $parts = array_map('ucfirst', $parts);
                $class = implode('_', $parts);

                if (!isset($this->map[$class])) {
                    try {
                        if (!isset($this->map[$class]) && (class_exists($class) || interface_exists($class))) {
                            $this->map[$class] = $item;
                        } else {
                            $class = str_replace('_', '\\', $class);
                            if (!isset($this->map[$class]) && (class_exists($class) || interface_exists($class))) {
                                $this->map[$class] = $item;
                            }
                        }
                    } catch (\Error $e) {
                        echo $e->getMessage() . "\n";
                    }
                }
            } else {
                $this->findClasses($item, $exceptPath);
            }
        }
    }

    /**
     * Find PHP Classes
     * @param string $path
     * @param string $exceptPath
     * @param string $baseSpace
     * @return void
     * @throws \Exception
     */
    protected function findPsr4Classes(string $path, string $exceptPath, string $baseSpace): void
    {
        $path = File::fillEndSep($path);

        $items = File::scanFiles($path, ['.php'], false);

        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            if (File::getExt($item) === '.php') {
                $parts = explode('/', str_replace($exceptPath, '', substr($item, 0, -4)));
                $parts = array_map('ucfirst', $parts);
                $class = $baseSpace . '\\' . implode('\\', $parts);

                if (!isset($this->map[$class])) {
                    $this->map[$class] = $item;
                }
            } else {
                $this->findPsr4Classes($item, $exceptPath, $baseSpace);
            }
        }
    }

    /**
     * save class map
     * @return boolean
     */
    public function save()
    {
        $writePath = Config::storage()->getWrite() . $this->autoloaderCfg['map'];
        return Utils::exportArray($writePath, $this->map);
    }
}
