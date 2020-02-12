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

namespace Dvelum;

use Dvelum\App\Service\Loader\LoaderInterface;
use Dvelum\Config\ConfigInterface;
use Exception;

class Service
{
    static protected $services = [];
    /**
     * @var ConfigInterface $config
     */
    static protected $config;
    /**
     * @var ConfigInterface $config
     */
    static protected $env;

    static public function register(ConfigInterface $config, ConfigInterface $env)
    {
        $data = $config->__toArray();
        $reIndexed = [];

        foreach($data as $k=>$v){
            $reIndexed[strtolower($k)] = $v;
        }

        $config->removeAll();
        $config->setData($reIndexed);

        self::$config = $config;
        self::$env = $env;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    static public function get(string $name)
    {
        $name = strtolower($name);

        if(!self::$config->offsetExists($name)){
            throw new Exception('Undefined service ' . $name);
        }

        if (!isset(self::$services[$name])) {
            $service = self::$config->get($name)['loader'];
            /**
             * @var LoaderInterface $instance
             */
            $instance = new $service();
            $instance->setConfig(self::$env);
            self::$services[$name] = $instance->loadService();
        }
        return self::$services[$name];
    }
}