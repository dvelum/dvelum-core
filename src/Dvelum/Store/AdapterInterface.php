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

namespace Dvelum\Store;

/**
 * Storage interface
 * @package Store
 * @author Kirill Egorov 2011
 */
interface AdapterInterface
{
    /**
     * Store value
     * @param string $key
     * @param mixed $val
     * @return void
     */
    public function set(string $key, $val): void;

    /**
     * Set values from array
     * @param array<string,mixed> $array
     * @return void
     */
    public function setValues(array $array): void;

    /**
     * Replace store data
     * @param array<string,mixed> $data
     * @return void
     */
    public function setData(array $data): void;

    /**
     * Get stored value by key
     * @param string $key
     * @return mixed
     */
    public function get(string $key);

    /**
     * Check if key exists
     * @param string $key
     * @return bool
     */
    public function keyExists(string $key): bool;

    /**
     * Remove data from storage
     * @param string $key
     * @return void
     */
    public function remove(string $key): void;

    /**
     * Clear storage.(Remove data)
     */
    public function clear(): void;

    /**
     * Get all storage data
     * @return array<string,mixed>
     */
    public function getData(): array;

    /**
     * Get records count
     * @return int
     */
    public function getCount(): int;
}
