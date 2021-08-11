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

namespace Dvelum\Template\Engine;

use Dvelum\Cache\CacheInterface;
use Dvelum\Config\ConfigInterface;
use Dvelum\Config\Storage\StorageInterface;
use Dvelum\Template\Storage;
use \Exception;


/**
 * View class
 * @author Kirill A Egorov 2011
 */
class ActiveTemplate implements EngineInterface
{
    /**
     * Template data (local variables)
     * @var array
     */
    private $data = [];
    /**
     * @var CacheInterface|null $cache
     */
    protected $cache = null;
    /**
     * @var bool $useCache
     */
    protected $useCache = false;

    /**
     * @var int|false $cacheLifetime
     */
    protected $cacheLifetime = false;

    protected Storage $storage;

    /**
     * @var ConfigInterface $config
     */
    protected $config;

    public function __construct(ConfigInterface $config, Storage $storage, ? CacheInterface $cache)
    {
        $this->config = $config;
        $this->cache = $cache;
        $this->storage = $storage;
    }


    /**
     * Template Render
     * @param string $templatePath — the path to the template file
     * @return string
     */
    public function render(string $templatePath): string
    {
        $hash = '';
        $realPath = $this->storage->get($templatePath);

        if(!$realPath){
            return '';
        }

        if($this->cache && $this->useCache)
        {
            $hash = md5('tpl_' . $templatePath . '_' . serialize($this->data).filemtime($realPath));
            $html = $this->cache->load($hash);

            if($html !== false)
                return (string)$html;
        }

        \ob_start();
        include $realPath;
        $result = \ob_get_clean();

        if($this->cache && $this->useCache){
            $this->cache->save($result , $hash, $this->cacheLifetime);
        }
        return (string) $result;
    }

    /**
     * Set property
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set(string $name , $value) : void
    {
        $this->data[$name] = $value;
    }

    /**
     * Set multiple properties
     * @param array $data
     * @return void
     */
    public function setProperties(array $data) : void
    {
        foreach ($data as $name=>$value)
            $this->data[$name] = $value;
    }

    /**
     * Get property
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        if(!isset($this->data[$name]))
            return null;

        return $this->data[$name];
    }

    public function __set($name , $value)
    {
        $this->set($name, $value);
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    /**
     * Empty template data
     * @return void
     */
    public function clear() : void
    {
        $this->data = [];
    }

    /**
     * Disable caching
     * @return void
     */
    public function disableCache() : void
    {
        $this->useCache = false;
    }

    /**
     * Enable caching
     * @return void
     */
    public function enableCache() : void
    {
        $this->useCache = true;
    }

    /**
     * Get template data
     * @return array
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * Redefine template data using an associative key-value array,
     * old and new data merge
     * @param array $data
     * @return void
     */
    public function setData(array $data) : void
    {
        $this->data = $data;
    }

    /**
     * Render sub template
     * @param string $templatePath
     * @param array $data
     * @param bool|true $useCache
     * @throws Exception
     * @return string
     */
    public function renderTemplate(string $templatePath, array $data = [], bool $useCache = true) : string
    {
        $tpl = \Dvelum\View::factory();
        $tpl->setData($data);

        if(!$useCache)
            $tpl->disableCache();

        return $tpl->render($templatePath);
    }

    /**
     * Set lifetime for cache data
     * @param int $sec
     */
    public function setCacheLifetime(int $sec) : void
    {
        $this->cacheLifetime = $sec;
    }
}