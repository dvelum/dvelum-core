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

namespace Dvelum;

use Dvelum\Config\Storage\StorageInterface;

class Lang
{
    protected $defaultDictionary = '';

    protected $storage = false;

    protected $dictionaries = [];
    protected $loaders = [];

    /**
     * Set default localization
     * @param string $name
     * @throws \Exception
     */
    public function setDefaultDictionary(string $name): void
    {
        if (!isset($this->dictionaries[$name]) && !isset($this->loaders[$name])) {
            throw new \Exception('Dictionary ' . $name . ' is not found');
        }

        $this->defaultDictionary = $name;
    }

    /**
     * Get default dictionary (lang)
     * @return string
     */
    public function getDefaultDictionary(): string
    {
        return $this->defaultDictionary;
    }

    /**
     * Add localization dictionary
     * @param string $name — localization name
     * @param Lang\Dictionary $dictionary — configuration object
     * @return void
     */
    public function addDictionary(string $name, Lang\Dictionary $dictionary): void
    {
        $this->dictionaries[$name] = $dictionary;
    }

    /**
     * Add localization loader
     * Backward compatibility
     * @param string $name - dictionary name
     * @param mixed $src - dictionary source
     * @param int $type - Config constant
     */
    static public function addDictionaryLoader(string $name, $src, int $type = Config\Factory::File_Array): void
    {
        /**
         * @var Lang $langService
         */
        $langService = Service::get('lang');
        $langService->addLoader($name, $src, $type);
    }

    /**
     * Add localization loader
     * @param string $name - dictionary name
     * @param mixed $src - dictionary source
     * @param int $type - Config constant
     */
    public function addLoader(string $name, $src, int $type = Config\Factory::File_Array): void
    {
        $this->loaders[$name] = array('src' => $src, 'type' => $type);
    }

    /**
     * Get localization dictionary by localization name or get default dictionary
     * @param string $name optional,
     * @throws \Exception
     * @return Lang\Dictionary
     */
    public function getDictionary(?string $name = null): Lang\Dictionary
    {
        if (empty($name)) {
            $name = $this->defaultDictionary;
        }

        if (isset($this->dictionaries[$name])) {
            return $this->dictionaries[$name];
        }

        if (!isset($this->dictionaries[$name]) && !isset($this->loaders[$name])) {
            throw new \Exception('Lang::lang Dictionary "' . $name . '" is not found');
        }

        $this->dictionaries[$name] = new Lang\Dictionary($name, $this->loaders[$name]);

        return $this->dictionaries[$name];
    }

    /**
     * Get link to localization dictionary by localization name or
     * get default dictionary
     * @param string $name optional,
     * @throws \Exception
     * @return Lang\Dictionary
     */
    static public function lang(?string $name = null): Lang\Dictionary
    {
        /**
         * @var Lang $langService
         */
        $langService = Service::get('lang');
        return $langService->getDictionary($name);
    }

    /**
     * Get configuration storage
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        if (!$this->storage) {
            $this->storage = new Config\Storage\File\AsArray();
        }
        return $this->storage;
    }

    /**
     * Get configuration storage
     * @return StorageInterface
     */
    static public function storage(): StorageInterface
    {
        /**
         * @var Lang $langService
         */
        $langService = Service::get('lang');
        return $langService->getStorage();
    }
}