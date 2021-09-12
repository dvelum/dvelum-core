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

namespace Dvelum\Template;

use Dvelum\Config\ConfigInterface;

class Storage
{
    /**
     * Runtime cache of configuration files
     * @var array<string,mixed>
     */
    protected static array $runtimeCache = [];

    /**
     * Storage configuration options
     * @var ConfigInterface<string,mixed>
     */
    protected ConfigInterface $config;

    /**
     * @param ConfigInterface<string,mixed> $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Set configuration options
     * @param array<string,mixed> $options
     * @return void
     */
    public function setConfig(array $options): void
    {
        foreach ($options as $k => $v) {
            $this->config[$k] = $v;
        }
    }

    /**
     * Get template real path  by local path
     * @param string $localPath
     * @param bool $useCache - optional
     * @return string | false
     */
    public function get(string $localPath, bool $useCache = true)
    {
        $key = $localPath;

        if (isset(static::$runtimeCache[$key]) && $useCache) {
            return static::$runtimeCache[$key];
        }

        $filePath = false;

        $list = $this->config['paths'];

        foreach ($list as $path) {
            if (!\file_exists($path . $localPath)) {
                continue;
            }

            $filePath = $path . $localPath;
            break;
        }

        if ($filePath === false) {
            return false;
        }

        if ($useCache) {
            static::$runtimeCache[$key] = $filePath;
        }

        return $filePath;
    }

    /**
     * Get template paths
     * @return array<int,string>
     */
    public function getPaths(): array
    {
        return $this->config['paths'];
    }

    /**
     * Add templates path
     * @param string $path
     * @return void
     */
    public function addPath($path): void
    {
        $this->config['paths'][] = $path;
    }

    /**
     * Set paths
     * @param array<int,string> $paths
     */
    public function setPaths(array $paths): void
    {
        $this->config['paths'] = $paths;
    }
}
