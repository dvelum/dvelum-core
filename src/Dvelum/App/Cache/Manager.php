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

namespace Dvelum\App\Cache;

use Dvelum\Cache\CacheInterface;

class Manager
{
    /**
     * @var array
     */
    static protected $connections = [];

    /**
     * Register cache adapter
     * @param string $name
     * @param CacheInterface $cache
     * @return void
     */
    public function register($name , CacheInterface $cache) : void
    {
        self::$connections[$name] = $cache;
    }
    
    /**
     * Get cache adapter
     * @param string $name
     * @return CacheInterface|bool
     */
    public function get(string $name)
    {
        if(!isset(self::$connections[$name]))
            return false;
        else
            return self::$connections[$name];
    }
    
    /**
     * Remove cache adapter
     * @param string $name
     * @return void
     */
    public function remove(string $name) : void
    {
        if(!isset(self::$connections[$name]))
            return;

        unset(self::$connections[$name]);
    }
    
    /**
     * Get list of registered adapters
     * @return array
     */
    public function getRegistered() : array
    {
        return self::$connections;
    }

    /**
     * Init Cache adapter by config
     * @param string $name
     * @param array $config
     * @return bool|CacheInterface
     */
    public function connect(string $name, array $config)
    {
        $cache = false;

        if(isset($config['enabled']) && $config['enabled'])
            $cache = new $config['backend']['name']($config['backend']['options']);

        self::$connections[$name] = $cache;

        return $cache;
    }
}