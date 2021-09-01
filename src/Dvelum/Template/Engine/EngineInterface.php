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

namespace Dvelum\Template\Engine;

use Dvelum\Cache\CacheInterface;
use Dvelum\Config\ConfigInterface;
use Dvelum\Config\Storage\StorageInterface;
use Dvelum\Template\Storage;

interface EngineInterface
{
    /**
     * @param ConfigInterface<string,mixed> $config
     * @param Storage $storage
     * @param CacheInterface|null $cache
     */
    public function __construct(ConfigInterface $config, Storage $storage, ?CacheInterface $cache);

    /**
     * Set lifetime for cache data
     * @param int $sec
     */
    public function setCacheLifetime(int $sec): void;

    /**
     * Get property
     * @param string $name
     * @return mixed
     */
    public function get(string $name);

    /**
     * Get property
     * @param string $name
     * @return mixed
     */
    public function __get($name);

    /**
     * Set property
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set(string $name, $value): void;

    /**
     * Set property
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value);

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name);

    /**
     * @param string $name
     * @return void
     */
    public function __unset($name);

    /**
     * Empty template data
     * @return void
     */
    public function clear(): void;

    /**
     * Disable caching
     * @return void
     */
    public function disableCache(): void;

    /**
     * Enable caching
     * @return void
     */
    public function enableCache(): void;

    /**
     * Get template data
     * @return array<string,mixed>
     */
    public function getData(): array;

    /**
     * Redefine template data using an associative key-value array,
     * old and new data merge
     * @param array<string,mixed> $data
     * @return void
     */
    public function setData(array $data): void;

    /**
     * Render current template
     * @param string $templatePath — the path to the template file
     * @return string
     */
    public function render(string $templatePath): string;

    /**
     * Render sub template
     * @param string $templatePath
     * @param array<string,mixed> $data
     * @param bool|true $useCache
     * @return string
     */
    public function renderTemplate(string $templatePath, array $data = [], bool $useCache = true): string;
}
