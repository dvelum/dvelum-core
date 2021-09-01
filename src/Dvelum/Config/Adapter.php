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
 * The Config_Abstract abstract class, which is used for implementing configuration adapters
 *  + backward compatibility with Config_Abstract
 * @author Kirill A Egorov
 * @abstract
 * @package Config
 */
class Adapter implements ConfigInterface
{
    /**
     * Parent config identifier
     * @var mixed
     */
    protected $applyTo = null;
    /**
     * Config Data
     * @var array<string,mixed>
     */
    protected array $data = [];

    /**
     * Config name
     * @var string|null
     */
    protected ?string $name;

    /**
     * Constructor
     * @param string|null $name - configuration identifier
     */
    public function __construct(?string $name = null)
    {
        $this->name = $name;
    }

    /**
     * Set configuration identifier
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Convert into an array
     * @return array<string,mixed>
     */
    public function __toArray(): array
    {
        return $this->data;
    }

    /**
     * Get the number of elements
     * @return integer
     */
    public function getCount(): int
    {
        return count($this->data);
    }

    /**
     * Get the configuration parameter
     * @param string $key — parameter name
     * @return mixed
     * @throws \Exception
     */
    public function get(string $key)
    {
        if (!isset($this->data[$key])) {
            throw new \Exception('Config::get Invalid key ' . $key);
        }

        return $this->data[$key];
    }

    /**
     *  Set the property value
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Set property values using an array
     * @param array<string,mixed> $data
     */
    public function setData(array $data): void
    {
        if (empty($data)) {
            return;
        }

        foreach ($data as $k => $v) {
            $this->data[$k] = $v;
        }
    }

    /**
     * Remove a parameter
     * @param string $key
     * @return void
     */
    public function remove(string $key): void
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }
    }

    /*
     * Start of ArrayAccess implementation
     */

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
    /*
     * End of ArrayAccess implementation
     */

    /*
     * Start of Iterator implementation
     */
    /**
     * @return void
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return $this->data[key($this->data)];
    }

    /**
     * @return bool|float|int|string|null
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * @return void
     */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->data[key($this->data)]);
    }
    /*
     * End of Iterator implementation
     */

    /**
     * Get data handle
     * Hack method. Do not use it without understanding.
     * Get a direct link to the stored data array
     * @return array<string,mixed>
     */
    public function & dataLink(): array
    {
        return $this->data;
    }

    /**
     * Remove all parameters
     */
    public function removeAll(): void
    {
        $this->data = [];
    }

    /**
     * Get config name
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->name;
    }

    /**
     * Get parent config identifier
     * @return string|null
     */
    public function getParentId(): ?string
    {
        return $this->applyTo;
    }

    /**
     * Set parent configuration identifier
     * @param string|null $id
     */
    public function setParentId(?string $id): void
    {
        $this->applyTo = $id;
    }
}
