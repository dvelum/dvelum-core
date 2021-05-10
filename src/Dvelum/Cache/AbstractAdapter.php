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

abstract class AbstractAdapter
{
    /**
     * @var string
     */
    protected $keyPrefix = '';
    /**
     * @var array
     */
    protected $stat = [
        'load' => 0 ,
        'add' => 0,
        'save' => 0 ,
        'remove' => 0
    ];

    /**
     * Adapter configuration
     * @var array
     */
    protected $settings = [];

    /**
     * AbstractAdapter constructor.
     * @param array $options - configuration options
     */
    public function __construct(array $options = [])
    {
        $this->settings = $this->initConfiguration($options);
        $this->connect($this->settings);
    }

    abstract protected function initConfiguration(array $options) : array;

    /**
     * @param array $settings
     * @return void
     */
    abstract protected function connect(array $settings) : void;

    /**
     * Prepare key, normalize, add prefix
     * @param mixed $key
     * @return string
     */
    protected function prepareKey($key) : string
    {
        $key = $this->keyPrefix . $key;

        if(!empty($this->settings['normalizeKeys']))
            $key = $this->normalize($key);

        return $key;
    }

    /**
     * Normalize key length
     * @param string $key
     * @return string
     */
    protected function normalize(string $key) : string
    {
        return md5($key);
    }

    /**
     * Get cache operations stats
     * @return array
     */
    public function getOperationsStat() : array
    {
        return $this->stat;
    }
}