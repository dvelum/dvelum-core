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

use Dvelum\Cache\CacheInterface;
use Dvelum\Config;
use Dvelum\Store\AdapterInterface;


/**
 * Configuration Object Factory
 * @author Kirill Yegorov 2010
 * @package Config
 */
class Factory
{
    const Simple = 0;
    const File_Array = 1;

    /**
     * @var AdapterInterface|bool
     */
    protected static $store = false;
    /**
     * @var CacheInterface|bool
     */
    protected static $cache = false;

    protected static $storageAdapter = '\\Dvelum\\Config\\Storage\\File\\AsArray';

    static public function setStorageAdapter(string $class) : void
    {
        if(!class_exists($class)){
            throw new \Exception('Undefined storage adapter '.$class);
        }
        self::$storageAdapter = $class;
    }
    /**
     * Set cache adapter
     * @param CacheInterface $core
     */
    public static function setCacheCore($core)
    {
        self::$cache = $core;
    }

    /**
     * Get cache adapter
     * @return CacheInterface | false
     */
    public static function getCacheCore()
    {
        return self::$cache;
    }

    /**
     * Factory method
     * @param integer $type -type of the object being created, Config class constant
     * @param string $name - identifier
     * @param boolean $useCache - optional , default true. Use cache if available
     * @return ConfigInterface
     */
    static public function config(int $type , string $name , bool $useCache = true) : ConfigInterface
    {
        $store = self::$store;
        $cache = self::$cache;

        if(!$store)
            $store = self::connectLocalStore();

        $config = false;
        $configKey = $type . '_' . $name;

        /*
         * Check if config is already loaded
         */
        if($useCache && $store->keyExists($configKey))
            return $store->get($configKey);

        /*
         * If individual keys
         */
        if($useCache && $cache && $config = $cache->load($configKey))
        {
            $store->set($configKey , $config);
            return $config;
        }

        switch($type)
        {
            case self::File_Array :
                $config =  static::storage()->get($name,$useCache);
                break;
            case self::Simple :
                $config = new Config\Adapter($name);
                break;
        }

        if($useCache)
            $store->set($configKey , $config);

        if($useCache && $cache)
            $cache->save($config , $configKey);
        else
            self::cache();

        return $config;
    }

    /**
     * Clear cache
     * @return void
     */
    static public function resetCache() : void
    {
        if(is_null(self::$store))
            self::connectLocalStore();

        if(empty(self::$store))
            return;

        foreach(self::$store as $k => $v)
        {
            if(self::$cache)
                self::$cache->remove($k);

            self::$store->remove($k);
        }
    }

    /**
     * Instantiate storage
     * @return AdapterInterface
     */
    static protected function connectLocalStore()
    {
        self::$store =  \Dvelum\Store\Factory::get( \Dvelum\Store\Factory::LOCAL , 'class_' . __CLASS__);
        return self::$store;
    }

    /**
     * Cache data again
     * @property mixed $key - optional
     * @return void
     */
    static public function cache($key = false)
    {
        if(!self::$cache)
            return;

        if($key === false)
        {
            foreach(self::$store as $k => $v)
            {
                self::$cache->save($v , $k);
            }
        }
        else
        {
            if(self::$store->keyExists($key))
            {
                self::$cache->save(self::$store->get($key), (string) $key);
            }
        }
    }

    /**
     * Get configuration storage
     * @param bool $force  - Reset runtime cache reload object, optional default false
     * @return Storage\StorageInterface
     */
    static public function storage($force = false) : Config\Storage\StorageInterface
    {
        static $store = false;

        if($force){
            $store = false;
        }

        if(!$store){
            /**
             * @var Config\Storage\StorageInterface $store;
             */
            $store = new self::$storageAdapter();
            if(!empty($config)){
                $store->setConfig($config->__toArray());
            }
        }
        return $store;
    }

    /**
     * Create new config object
     * @param array $data
     * @param string|null $name
     * @return ConfigInterface
     */
    static public function create(array $data, ?string $name = null) : ConfigInterface
    {
        $config = new Config\Adapter($name);
        $config->setData($data);
        return $config;
    }
}