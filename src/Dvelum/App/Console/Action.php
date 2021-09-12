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

namespace Dvelum\App\Console;

use Dvelum\Config\ConfigInterface;
use Psr\Container\ContainerInterface;

abstract class Action implements ActionInterface
{
    /**
     * Main application config
     * @var ConfigInterface<string,mixed>
     */
    protected ConfigInterface $appConfig;
    /**
     * @var array<string,mixed>
     */
    protected array $config;
    /**
     * @var array<string,mixed>
     */
    protected array $stat = [];
    /**
     * Action params
     * @var array<int,mixed>
     */
    protected array $params = [];
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $diContainer;

    /**
     * @param ConfigInterface<string,mixed> $appConfig
     * @param array<int,mixed> $params
     * @param array<string,mixed> $config
     * @return void
     */
    public function init(
        ContainerInterface $diContainer,
        ConfigInterface $appConfig,
        array $params = [],
        array $config = []
    ): void {
        $this->appConfig = $appConfig;
        $this->params = $params;
        $this->config = $config;
        $this->diContainer = $diContainer;
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

    public function run(): bool
    {
        $t = microtime(true);
        $result = $this->action();
        $this->stat['time'] = number_format(microtime(true) - $t, 5) . 's.';
        return $result;
    }

    /**
     * @return bool
     */
    abstract protected function action(): bool;
}
