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

namespace Dvelum\Template;

use Dvelum\Cache\CacheInterface;
use Dvelum\Config\Factory as ConfigFactory;
use Dvelum\Config\ConfigInterface;
use Dvelum\Template\Engine\EngineInterface;

class Service
{
    /**
     * @var string $adapterClass
     */
    protected $adapterClass;

    /**
     * @var ConfigInterface<string,mixed> $engineConfig
     */
    protected ConfigInterface $engineConfig;

    /**
     * @var CacheInterface|null
     */
    protected ?CacheInterface $cache = null;

    protected Storage $storage;

    /**
     * Service constructor.
     * @param ConfigInterface<string,mixed> $config
     * @param CacheInterface|null $cache
     * @throws \Exception
     */
    public function __construct(ConfigInterface $config, Storage $storage, ?CacheInterface $cache)
    {
        $this->adapterClass = $config->get('template_engine');
        $this->engineConfig = ConfigFactory::create($config->get('engine_config'));
        $this->cache = $cache;
        $this->storage = $storage;
    }

    /**
     * @return EngineInterface
     */
    public function getTemplate(): EngineInterface
    {
        /**
         * @var EngineInterface $adapter
         */
        $adapter = new $this->adapterClass($this->engineConfig, $this->storage, $this->cache);

        return $adapter;
    }
}
