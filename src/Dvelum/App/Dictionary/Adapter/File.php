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

namespace Dvelum\App\Dictionary\Adapter;

use Dvelum\App\Dictionary\DictionaryInterface;
use Dvelum\Lang;
use Dvelum\Config;

class File implements DictionaryInterface
{
    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var Config\ConfigInterface
     */
    protected $data = [];

    public function __construct(string $name , Config\ConfigInterface $config)
    {
        $this->name = $name;

        $configPath = $config->get('configPath') . $name . '.php';

        if(!Config::storage()->exists($configPath))
            Config::storage()->create($configPath);

        $this->data = Config::storage()->get($configPath, true, false);
    }

    /**
     * Get dictionary name
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Check if the key exists in the dictionary
     * @param string $key
     * @return bool
     */
    public function isValidKey(string $key) : bool
    {
        return $this->data->offsetExists($key);
    }

    /**
     * Get value by key
     * @param string $key
     * @return string
     */
    public function getValue(string $key) : string
    {
        return $this->data->get($key);
    }

    /**
     * Get dictionary data
     * @return array
     */
    public function getData() : array
    {
        return $this->data->__toArray();
    }

    /**
     * Add a record
     * @param string $key
     * @param string $value
     * @return void
     */
    public function addRecord(string $key , string $value) : void
    {
        $this->data->set($key, $value);
    }

    /**
     * Delete record by key
     * @param string $key
     * @return void
     */
    public function removeRecord(string $key) : void
    {
       $this->data->remove($key);
    }

    /**
     * Get dictionary as JavaScript code representation
     * @param boolean $addAll - add value 'All' with a blank key,
     * @param boolean $addBlank - add empty value is used in drop-down lists
     * @param string|boolean $allText, optional - text for not selected value
     * @return string
     */
    public function __toJs($addAll = false , $addBlank = false , $allText = false) : string
    {
        $result = [];

        if($addAll){
            if($allText === false){
                $allText = Lang::lang()->get('ALL');
            }
            $result[] = ['id' => '' , 'title' => $allText];
        }

        if(!$addAll && $addBlank)
            $result[] = ['id' => '' , 'title' => ''];

        foreach($this->data as $k => $v)
            $result[] = ['id' => strval($k) , 'title' => $v];

        return json_encode($result);
    }

    /**
     * Get key for value
     * @param $value
     * @param boolean $i case insensitive
     * @return mixed, false on error
     */
    public function getKeyByValue(string $value, $i = false)
    {
        foreach($this->data as $k=>$v)
        {
            if($i){
                $v = strtolower($v);
                $value = strtolower($value);
            }
            if($v === $value){
                return $k;
            }
        }
        return false;
    }

    /**
     * Save dictionary
     * @return bool
     */
    public function save() : bool
    {
        $storage = Config::storage();
        return $storage->save($this->data);
    }
}