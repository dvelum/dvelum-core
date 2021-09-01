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

namespace Dvelum\Config;

/**
 * Interface ConfigInterface
 * @extends  \ArrayAccess<string,mixed>
 * @extends \Iterator<string,mixed>
 */
interface ConfigInterface extends \ArrayAccess, \Iterator
{
    public function __construct(string $name);

    /**
     * Convert into an array
     * @return array<int|string,mixed>
     */
    public function __toArray(): array;

    /**
     * Get the number of elements
     * @return int
     */
    public function getCount(): int;

    /**
     * Get the configuration parameter
     * @param string $key — parameter name
     * @return mixed
     * @throws \Exception
     */
    public function get(string $key);

    /**
     *  Set the property value
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value): void;

    /**
     * Set property values using an array
     * @param array<int|string,mixed> $data
     */
    public function setData(array $data): void;

    /**
     * Remove a parameter
     * @param string $key
     * @return void
     */
    public function remove(string $key):void;

    /**
     * Get data handle
     * Hack method. Do not use it without understanding.
     * Get a direct link to the stored data array
     * @return array<int|string,mixed>
     */
    public function & dataLink(): array;

    /**
     * Remove all parameters
     */
    public function removeAll(): void;

    /**
     * Get config name
     * @return string
     */
    public function getName(): string;

    /**
     * Get parent config identifier
     * @return string|null
     */
    public function getParentId(): ?string;

    /**
     * Set parent configuration identifier
     * @param string|null $id
     */
    public function setParentId(?string $id): void;
}
