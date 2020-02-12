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

namespace Dvelum\App\Code\Minify;

use Dvelum\App\Code\Minify\Adapter\AdapterInterface;
use Dvelum\Config;
use Dvelum\Config\ConfigInterface;

class Minify
{
    /**
     * @var ConfigInterface $config
     */
    protected $config;

    static public function factory()
    {
        $config = Config::storage()->get('minify.php');
        return new static($config);
    }

    protected function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Minify JS string
     * @param string $string
     * @return string
     */
    public function minifyJs(string $string) : string
    {
        $adapterClass = $this->config->get('js')['adapter'];
        /**
         * @var AdapterInterface $adapter
         */
        $adapter = new $adapterClass;
        return $adapter->minify($string);
    }

    /**
     * Combine and minify code files
     * @param array $files
     * @param string $toFile
     * @return bool
     */
    public function minifyJsFiles(array $files, string $toFile) : bool
    {
        $adapterClass = $this->config->get('js')['adapter'];
        /**
         * @var AdapterInterface $adapter
         */
        $adapter = new $adapterClass;
        return $adapter->minifyFiles($files, $toFile);
    }

    /**
     * Minify CSS string
     * @param string $string
     * @return string
     */
    public function minifyCss(string $string) : string
    {
        $adapterClass = $this->config->get('css')['adapter'];
        /**
         * @var AdapterInterface $adapter
         */
        $adapter = new $adapterClass;
        return $adapter->minify($string);
    }

    /**
     * Combine and minify code files
     * @param array $files
     * @param string $toFile
     * @return bool
     */
    public function minifyCssFiles(array $files, string $toFile) : bool
    {
        $adapterClass = $this->config->get('css')['adapter'];
        /**
         * @var AdapterInterface $adapter
         */
        $adapter = new $adapterClass;
        return $adapter->minifyFiles($files, $toFile);
    }
}