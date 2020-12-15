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

namespace Dvelum\Lang;

use Dvelum\Lang;
use Dvelum\Config;
use Dvelum\Config\ConfigInterface;

class Dictionary
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var array
     */
    protected $loader;

    /**
     * @var ConfigInterface
     */
    protected $data;

    /**
     * @param string $name
     * @param array $loaderConfig
     */
    public function __construct(string $name, array $loaderConfig)
    {
        $this->loader = $loaderConfig;
        $this->name = $name;
    }

    /**
     * Get a localized string by dictionary key.
     * If the necessary key is absent, the following value will be returned: «[key]»
     * @param string $key
     * @return string
     */
    public function get(string $key): string
    {
        if (!isset($this->data)) {
            $this->loadData();
        }

        if ($this->data->offsetExists($key)) {
            return $this->data->get($key);
        }

        return '[' . $key . ']';
    }

    /**
     * @param string $key
     * @return string
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        if (!!isset($this->data)) {
            $this->loadData();
        }

        return $this->data->offsetExists($key);
    }

    /**
     * Convert the localization dictionary to JSON
     * @return string
     */
    public function getJson(): string
    {
        if (!isset($this->data)) {
            $this->loadData();
        }

        return (string)\json_encode($this->data->__toArray());
    }

    /**
     * Convert the localization dictionary to JavaScript object
     * @return string
     */
    public function getJsObject(): string
    {
        if (!isset($this->data)) {
            $this->loadData();
        }

        $items = [];
        foreach ($this->data as $k => $v) {
            $items[] = $k . ':"' . $v . '"';
        }

        return \str_replace("\n", "", '{' . implode(',', $items) . '}');
    }

    /**
     * Load dictionary data
     * @return void
     */
    protected function loadData(): void
    {
        switch ($this->loader['type']) {
            case Config\Factory::File_Array:
                $this->data = Lang::storage()->get($this->loader['src'], true, true);
                break;
        }
    }

    /**
     * Get current dictionary name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}