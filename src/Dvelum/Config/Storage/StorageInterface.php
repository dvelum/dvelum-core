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

namespace Dvelum\Config\Storage;

use Dvelum\Config\ConfigInterface;

interface StorageInterface
{
    /**
     * @param string $localPath
     * @param bool $useCache
     * @param bool $merge
     * @return ConfigInterface<int|string,mixed>
     */
    public function get(string $localPath, bool $useCache = true, bool $merge = true): ConfigInterface;

    /**
     * Create new config file
     * @param string $id
     * @return bool
     */
    public function create(string $id): bool;

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
     * @return array<int|string|int,mixed>
     */
    public function getList($path = false, bool $recursive = false): array;

    /**
     * Check if config file exists
     * @param string $localPath
     * @return bool
     */
    public function exists(string $localPath): bool;

    /**
     * Get storage paths
     * @return array<int,string>
     */
    public function getPaths(): array;

    /**
     * Add config path
     * @param string $path
     * @return void
     */
    public function addPath(string $path): void;

    /**
     * Prepend config path
     * @param string $path
     * @return void
     */
    public function prependPath(string $path): void;

    /**
     * Get write path
     * @return string
     */
    public function getWrite(): string;

    /**
     * Get src file path (to apply)
     * @return string
     */
    public function getApplyTo(): string;

    /**
     * Get debug information. (loaded configs)
     * @return array<int,string>
     */
    public function getDebugInfo(): array;

    /**
     * Set configuration options
     * @param array<string,mixed> $options
     * @return void
     */
    public function setConfig(array $options): void;

    /**
     * Save configuration data
     * @param ConfigInterface<int|string,mixed> $config
     * @return bool
     */
    public function save(ConfigInterface $config): bool;

    /**
     * Reset cached configs
     */
    public function resetConfigCache(): void;

    /**
     * Replace paths data
     * @param array<int,string> $paths
     */
    public function replacePaths(array $paths): void;
}
