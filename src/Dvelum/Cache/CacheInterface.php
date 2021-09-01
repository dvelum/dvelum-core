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

namespace Dvelum\Cache;

/**
 * Interface for cache adapters
 * @package Dvelum\Cache
 */
interface CacheInterface
{
    /**
     * Add / replace cache variable
     * @param mixed $data
     * @param string $key
     * @param int|false $lifetime - optional
     * @return bool
     */
    public function save($data, string $key, $lifetime = false) : bool ;

    /**
     * @param string $id
     * @param mixed $data
     * @param int|false $specificLifetime
     * @return bool
     */
    public function add(string $id, $data, $specificLifetime = false) : bool;

    /**
     * Load cached variable
     * @param string $key
     * @return mixed|false
     */
    public function load(string $key);

    /**
     * Clear cache
     * @return bool
     */
    public function clean() : bool;

    /**
     * Remove cached variable
     * @param string $key
     * @return bool
     */
    public function remove(string $key) : bool;

    /**
     * Get statistics for cache operation
     * @return array
     */
    public function getOperationsStat() : array;
}