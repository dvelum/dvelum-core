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

namespace Dvelum\App\Console;

use Dvelum\Config\ConfigInterface;

abstract class Action implements ActionInterface
{
    /**
     * Main application config
     * @var ConfigInterface
     */
    protected $appConfig;
    protected $config;
    protected $stat = [];
    /**
     * Action params
     * @var array
     */
    protected $params = [];

    /**
     * @param ConfigInterface $appConfig
     * @param array $params
     * @param array $config
     * @return void
     */
    public function init(ConfigInterface $appConfig, array $params = [], array $config = []): void
    {
        $this->appConfig = $appConfig;
        $this->params = $params;
        $this->config = $config;
    }

    /**
     * Get job statistics as string
     * (useful for logs)
     * @return string
     */
    public function getInfo(): string
    {
        $s = '';
        foreach ($this->stat as $k => $v) {
            $s .= $k . ' : ' . $v . '; ';
        }

        return $s;
    }

    public function run() : bool
    {
        $t = microtime(true);
        $result = $this->action();
        $this->stat['time'] = number_format(microtime(true) - $t, 5).'s.';
        return $result;
    }

    /**
     * @return bool
     */
    abstract protected function action(): bool;
}