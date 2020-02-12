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

namespace Dvelum\Config\Storage;

use Dvelum\Config\ConfigInterface;

interface StorageInterface
{
    public function get(string $localPath , bool $useCache = true , bool $merge = true) : ConfigInterface;

    /**
     * Create new config file
     * @param string $id
     * @return boolean
     */
    public function create(string $id) : bool;

    /**
     * Find path for config file (no merge)
     * @param string $localPath
     * @return string | bool
     */
    public function getPath(string $localPath);

    /**
     * Get list of available configs
     * @param string|bool $path - optional, default false
     * @param bool $recursive - optional, default false
     * @return array
     */
    public function getList($path = false, $recursive = false) : array;

    /**
     * Check if config file exists
     * @param string $localPath
     * @return bool
     */
    public function exists(string $localPath) : bool;

    /**
     * Get storage paths
     * @return array
     */
    public function getPaths() : array;

    /**
     * Add config path
     * @param string $path
     * @return void
     */
    public function addPath(string $path) : void;

    /**
     * Prepend config path
     * @param string $path
     * @return void
     */
    public function prependPath(string $path) : void;

    /**
     * Get write path
     * @return string
     */
    public function getWrite() : string;

    /**
     * Get src file path (to apply)
     * @return string
     */
    public function getApplyTo() : string;

    /**
     * Get debug information. (loaded configs)
     * @return array
     */
    public function getDebugInfo() : array;

    /**
     * Set configuration options
     * @param array $options
     * @return void
     */
    public function setConfig(array $options) : void;

    /**
     * Save configuration data
     * @param ConfigInterface $config
     * @return bool
     */
    public function save(ConfigInterface $config) : bool;

    /**
     * Reset cached configs
     */
    public function resetConfigCache() : void;

    /**
     * Replace paths data
     * @param array $paths
     */
    public function replacePaths(array $paths): void;
}