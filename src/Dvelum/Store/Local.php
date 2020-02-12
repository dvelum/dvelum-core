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

namespace Dvelum\Store;
/**
 * The class allows you to store data locally in the form of key value pairs
 * Note that null value causes the keyExists() method return false (for better perfomance)
 * @author Kirill Yegorov 2008
 * @package Store
 */
class Local implements AdapterInterface
{
    protected $storage;
    protected $name;

    /**
     * Local constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->storageConnect();
    }

    /**
     * Instantiate storage
     * @return void
     */
    protected function storageConnect()
    {
        $this->storage = [];
    }

    /**
     * (non-PHPdoc)
     * @see Dvelum/Store/Store_Interface#getData()
     */
    public function getData() : array
    {
        return $this->storage;
    }

    /**
     * Get items count
     * @return integer
     */
    public function getCount() : int
    {
        return count($this->storage);
    }

    /**
     * (non-PHPdoc)
     * @see www/library/Store/AdapterInterface#get($key)
     */
    public function get($key)
    {
        if(isset($this->storage[$key]))
            return $this->storage[$key];
        return null;
    }

    /**
     * Note that null value causes keyExists return false (for better perfomance)
     * (non-PHPdoc)
     * @see www/library/Store/Store_Interface#set($key, $val)
     */
    public function set($key , $value)
    {
        $this->storage[$key] = $value;
    }

    /**
     * (non-PHPdoc)
     * @see www/library/Store/Store_Interface#setValues($array)
     */
    public function setValues(array $array)
    {
        foreach($array as $k => $v)
            $this->set($k , $v);
    }

    /**
     * Note that null value causes the keyExists() method return false (for better perfomance)
     * (non-PHPdoc)
     * @see www/library/Store/Store_Interface#keyExists($key)
     */
    public function keyExists($key) : bool
    {
        return isset($this->storage[$key]);
    }

    /**
     * (non-PHPdoc)
     * @see www/library/Store/Store_Interface#remove($key)
     */
    public function remove($key) : void
    {
        unset($this->storage[$key]);
    }

    /**
     * (non-PHPdoc)
     * @see AdapterInterface::clear()
     */
    public function clear() : void
    {
        $this->storage = [];
    }
    /**
     * Replace store data
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->storage = $data;
    }
}