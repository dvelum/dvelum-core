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

namespace Dvelum\App\Dictionary;

use Dvelum\App\Dictionary;
use Dvelum\Cache\CacheInterface;
use Dvelum\Config;
use Dvelum\Config\ConfigInterface;
use Dvelum\File;
use Dvelum\Lang;
use Dvelum\Config\Storage\StorageInterface;

class Manager
{
    public const CACHE_KEY_LIST = 'Dictionary_Manager_list';
    public const CACHE_KEY_DATA_HASH = 'Dictionary_Manager_dataHash';

    /**
     * Base path
     * @var string
     */
    protected string $baseDir = '';

    /**
     * Path to localized dictionary
     * @var string
     */
    protected string $path = '';

    protected ?CacheInterface $cache = null;
    /**
     * @var string
     */
    protected string $language = '';
    /**
     * @var array<string,string>|null
     */
    protected static $list = null;

    /**
     * Valid dictionary local cache
     * @var array<string,bool>
     */
    protected static array $validDictionary = [];

    /**
     * @var ConfigInterface<string,mixed>
     */
    protected ConfigInterface $appConfig;

    protected Lang $lang;

    protected StorageInterface $configStorage;

    protected Service $service;

    /**
     * @param Lang $lang
     * @param ConfigInterface<int|string,mixed> $appConfig
     * @param StorageInterface $configStorage
     * @param Service $service
     * @param CacheInterface|null $cache
     * @throws \Exception
     */
    public function __construct(
        Lang $lang,
        ConfigInterface $appConfig,
        StorageInterface $configStorage,
        Service $service,
        ?CacheInterface $cache = null
    ) {
        $this->service = $service;
        $this->lang = $lang;
        $this->appConfig = $appConfig;
        $this->language = (string)$appConfig->get('language');
        $this->path = $configStorage->getWrite();
        $this->baseDir = $appConfig->get('dictionary_folder');
        $this->cache = $cache;
        $this->configStorage = $configStorage;

        if ($this->cache && $list = $this->cache->load(self::CACHE_KEY_LIST)) {
            self::$list = $list;
        }
    }

    /**
     * Get list of dictionaries
     * @return array<int,string>
     * @throws \Exception
     */
    public function getList(): array
    {
        if (!is_null(self::$list)) {
            return array_keys(self::$list);
        }

        $paths = $this->configStorage->getPaths();

        $list = [];

        foreach ($paths as $path) {
            if (!file_exists($path . $this->baseDir . 'index/')) {
                continue;
            }

            $files = File::scanFiles($path . $this->baseDir . 'index/', ['.php'], false, File::FILES_ONLY);

            if (!empty($files)) {
                foreach ($files as $itemPath) {
                    $name = substr(basename($itemPath), 0, -4);
                    $list[$name] = $itemPath;
                }
            }
        }

        self::$list = $list;

        if ($this->cache) {
            $this->cache->save(self::CACHE_KEY_LIST, $list);
        }

        return array_keys($list);
    }

    /**
     * Create dictionary
     * @param string $name
     * @param string|bool $language - optional
     * @return bool
     */
    public function create(string $name, $language = false): bool
    {
        if ($language === false) {
            $language = $this->language;
        }

        $indexFile = $this->path . $this->baseDir . 'index/' . $name . '.php';
        $dictionaryFile = $this->path . $this->baseDir . $language . '/' . $name . '.php';

        if (!file_exists($dictionaryFile)) {
            $dictionaryConfig = Config\Factory::create([], $dictionaryFile);
            if ($this->configStorage->save($dictionaryConfig)) {
                if (!file_exists($indexFile)) {
                    $indexConfig = Config\Factory::create([], $indexFile);
                    if (!$this->configStorage->save($indexConfig)) {
                        return false;
                    }
                }
                self::$validDictionary[$name] = true;
                $this->resetCache();
                return true;
            }
        }
        return false;
    }

    /**
     * Rename dictionary
     * @param string $oldName
     * @param string $newName
     * @return bool
     */
    public function rename(string $oldName, string $newName): bool
    {
        $dirs = File::scanFiles($this->path . $this->baseDir, false, false, File::DIRS_ONLY);

        foreach ($dirs as $path) {
            if (file_exists($path . '/' . $oldName . '.php')) {
                if (!@rename($path . '/' . $oldName . '.php', $path . '/' . $newName . '.php')) {
                    return false;
                }
            }
        }

        if (isset(self::$validDictionary[$oldName])) {
            unset(self::$validDictionary[$oldName]);
        }

        self::$validDictionary[$newName] = true;

        $this->resetCache();

        return true;
    }

    /**
     * Check if dictionary exists
     * @param string $name
     * @return bool
     */
    public function isValidDictionary(string $name): bool
    {
        /*
         * Check local cache
         */
        if (isset(self::$validDictionary[$name])) {
            return true;
        }

        if ($this->configStorage->exists($this->baseDir . 'index/' . $name . '.php')) {
            self::$validDictionary[$name] = true;
            return true;
        }
        return false;
    }

    /**
     * Remove dictionary
     * @param string $name
     * @return bool
     */
    public function remove(string $name): bool
    {
        $dirs = File::scanFiles($this->path . $this->baseDir, false, false, File::DIRS_ONLY);

        foreach ($dirs as $path) {
            $file = $path . '/' . $name . '.php';
            if (file_exists($file) && is_file($file)) {
                if (!@unlink($file)) {
                    return false;
                }
            }
        }

        if (isset(self::$validDictionary[$name])) {
            unset(self::$validDictionary[$name]);
        }

        $this->resetCache();

        return true;
    }

    /**
     * Reset cache
     */
    public function resetCache(): void
    {
        if (!$this->cache) {
            return;
        }

        $this->cache->remove(self::CACHE_KEY_LIST);
        $this->cache->remove(self::CACHE_KEY_DATA_HASH);
    }

    /**
     * Get data hash (all dictionaries data)
     * @return string
     */
    public function getDataHash(): string
    {
        if ($this->cache) {
            $hash = $this->cache->load(self::CACHE_KEY_DATA_HASH);
            if (!empty($hash) && is_string($hash)) {
                return $hash;
            }
        }

        $s = '';

        $list = $this->getList();

        if (!empty($list)) {
            foreach ($list as $name) {
                $s .= $name . ':' . Dictionary::factory($name)->__toJs();
            }
        }

        $s = md5($s);

        if ($this->cache) {
            $this->cache->save(self::CACHE_KEY_DATA_HASH, $s);
        }

        return $s;
    }


    /**
     * Save changes
     * @param string $name
     * @return bool
     */
    public function saveChanges(string $name): bool
    {
        $dict = $this->service->get($name);
        if (!$dict->save()) {
            return false;
        }

        $this->resetCache();
        $this->rebuildIndex($name);
        $this->mergeLocales($name, $this->language);
        return true;
    }

    /**
     * Rebuild dictionary index
     * @param string $name
     * @return bool
     */
    public function rebuildIndex(string $name): bool
    {
        $dict = $this->service->get($name);

        $filePath = $this->baseDir . 'index/' . $name . '.php';
        $index = $this->configStorage->get($filePath, false, false);

        $index->removeAll();
        $index->setData(array_keys($dict->getData()));
        $this->configStorage->save($index);

        return true;
    }

    /**
     * Sync localized versions of dictionaries using base dictionary as a reference list of records
     * @param string $name
     * @param string $baseLocale
     * @return bool
     */
    public function mergeLocales(string $name, string $baseLocale): bool
    {
        $baseDict = $this->configStorage->get($this->baseDir . $baseLocale . '/' . $name . '.php', false, false);

        $locManager = new \Dvelum\App\Localization\Manager($this->appConfig, $this->lang);

        foreach ($locManager->getLangs(true) as $locale) {
            if ($locale == $baseLocale) {
                continue;
            }

            $localPath = $this->baseDir . $locale . '/' . $name . '.php';

            if (!$this->configStorage->exists($localPath)) {
                if (!$this->create($name, $locale)) {
                    return false;
                }
            }

            $dict = $this->configStorage->get($localPath, false, false);

            // Add new records from base dictionary and remove redundant records from current
            $mergedData = array_merge(
            // get elements from current dictionary with keys common for arrays
                array_intersect_key($dict->__toArray(), $baseDict->__toArray()),
                // get new records for current dictionary
                array_diff_key($baseDict->__toArray(), $dict->__toArray())
            );

            $dict->removeAll();
            $dict->setData($mergedData);
            $this->configStorage->save($dict);
        }
        return true;
    }
}
