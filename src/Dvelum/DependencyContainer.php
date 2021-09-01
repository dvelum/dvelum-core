<?php

/**
 * DVelum project https://github.com/dvelum/dvelum-core , https://github.com/dvelum/dvelum
 *
 * MIT License
 *
 * Copyright (C) 2011-2021  Kirill Yegorov
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

use Dvelum\DependencyContainer\ArgumentInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 *
 * @package App\Service
 */
class DependencyContainer implements ContainerInterface
{
    /**
     * @var array<string,Object>
     */
    protected array $container;
    /**
     * @var array<string,mixed>
     */
    protected array $lazyInit = [];

    /**
     * @param string $interfaceName
     * @param mixed $value
     */
    public function bind(string $interfaceName, $value): void
    {
        $this->container[$interfaceName] = $value;
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function get(string $id)
    {
        if (isset($this->container[$id])) {
            return $this->container[$id];
        }

        if (isset($this->lazyInit[$id])) {
            $this->container[$id] = $this->loadDependency($id);
            return $this->container[$id];
        }

        throw new RuntimeException('Unresolved runtime dependency ' . $id);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return (isset($this->container[$id]) || isset($this->lazyInit[$id]));
    }

    /**
     * @param array<string,mixed> $config
     */
    public function bindArray(array $config): void
    {
        $this->lazyInit = array_merge($this->lazyInit, $config);
    }

    /**
     * @param string $key
     * @return mixed|object
     * @throws \ReflectionException
     */
    private function loadDependency(string $key)
    {
        $object = $this->lazyInit[$key];

        if (is_callable($object)) {
            return $object($this);
        }

        if (is_object($object)) {
            return $object;
        }

        if (is_string($object)) {
            return new $object();
        }

        if (is_array($object)) {
            if (!isset($object['class'])) {
                throw new \InvalidArgumentException('Invalid dependency definition. ' . $key . ' expect class key');
            }
            if (isset($object['arguments']) && is_array($object['arguments'])) {
                $arguments = [];
                foreach ($object['arguments'] as $item) {
                    if ($item instanceof ArgumentInterface) {
                        $arguments[] = $item->get($this);
                    } else {
                        $arguments[] = $item;
                    }
                }
            } else {
                $arguments = [];
            }
            $rc = new \ReflectionClass($object['class']);
            return $rc->newInstanceArgs($arguments);
        }
        throw new RuntimeException('Cannot load dependency ' . $key);
    }
}
