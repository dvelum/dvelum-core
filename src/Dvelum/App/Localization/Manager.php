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

namespace Dvelum\App\Localization;

use Dvelum\Config\ConfigInterface;
use Dvelum\Config\Factory;
use Dvelum\Service;
use Dvelum\Lang;
use Exception;

/**
 * Class Manager
 * @package Dvelum\App\Backend\Localization
 */
class Manager
{
    /**
     * @var ConfigInterface<string,mixed>
     */
    protected ConfigInterface $appConfig;
    /**
     * Message language
     * @var Lang\Dictionary
     */
    protected Lang\Dictionary $lang;
    /**
     * @var string
     */
    protected string $indexLanguage = 'en';

    /**
     * Localizations file path
     * @var array<int,string>
     */
    protected array $langsPaths;

    protected Lang $langService;

    /**
     * @param ConfigInterface<string,mixed> $appConfig
     */
    public function __construct(ConfigInterface $appConfig, Lang $lang)
    {
        $this->langService = $lang;
        $this->appConfig = $appConfig;
        $this->langsPaths = $lang->getStorage()->getPaths();
        $this->lang = $lang->getDictionary();
    }

    /**
     * Get list of system languages
     * @param bool $onlyMain - optional. Get only global locales without subpackages
     * @return array<int,string>
     */
    public function getLangs(bool $onlyMain = true): array
    {
        $langStorage = $this->langService->getStorage();
        $langs = $langStorage->getList(false, !$onlyMain);
        $paths = $langStorage->getPaths();

        $data = [];
        foreach ($langs as $file) {
            $file = str_replace($paths, '', $file);
            if (
                strpos($file, 'index') === false &&
                basename($file) !== 'objects.php' &&
                strpos($file, '/objects/') == false
            ) {
                $data[] = substr($file, 0, -4);
            }
        }
        return array_unique($data);
    }

    /**
     * Rebuild all localization indexes
     * @return void
     */
    public function rebuildAllIndexes(): void
    {
        $this->rebuildIndex(false);
        $sub = $this->getSubPackages();
        foreach ($sub as $pack) {
            $this->rebuildIndex($pack);
        }
    }

    /**
     * Get language subpackages
     * @param string|bool $lang - optional
     * @return array<int,string>
     */
    public function getSubPackages($lang = false) : array
    {
        $data = [];

        if (!$lang) {
            $lang = $this->indexLanguage;
        }

        if ($lang) {
            $lang = $lang . '/';
        } else {
            $lang = false;
        }

        $langs = $this->langService->getStorage()->getList($lang, false);

        foreach ($langs as $file) {
            if (basename($file) !== 'objects.php') {
                $data[] = substr(basename($file), 0, -4);
            }
        }
        return $data;
    }

    /**
     * Get list of sub dictionaries (names only)
     * @return array<int,string>
     */
    public function getSubDictionaries(): array
    {
        $language = $this->langService->getDefaultDictionary();

        $result = $this->getSubPackages($language);

        if (!empty($result)) {
            foreach ($result as &$v) {
                $v = str_replace(array('/', '\\'), '', $v);
            }
        } else {
            $result = [];
        }
        return $result;
    }

    /**
     * Rebuild language index
     * @param string | bool $subPackage - optional
     * @return void
     * @throws \Exception
     */
    public function rebuildIndex($subPackage = false): void
    {
        $indexFile = '';

        if (empty($subPackage)) {
            $indexName = $this->getIndexName();
            $indexBaseName = $this->indexLanguage . '.php';
        } else {
            $indexName = $this->getIndexName((string)$subPackage);
            $indexBaseName = $this->indexLanguage . '/' . $subPackage . '.php';
        }

        try {
            $indexBase = $this->langService->getStorage()->get($indexBaseName);
        } catch (\Throwable $e) {
            throw new Exception($this->lang->get('CANT_LOAD') . ' ' . $indexBaseName);
        }

        $baseKeys = array_keys($indexBase->__toArray());
        $storage = $this->langService->getStorage();
        $indexPath = $storage->getPath($indexName);
        $writePath = $storage->getWrite();
        if (!file_exists((string)$indexPath) && !file_exists($writePath . $indexName)) {
            if (!\Dvelum\Utils::exportArray($writePath . $indexFile, [])) {
                throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $writePath . $indexName);
            }
        }

        try {
            $indexConfig = $storage->get($indexName);
        } catch (\Throwable $e) {
            throw new Exception($this->lang->get('CANT_LOAD') . ' ' . $indexName);
        }

        $indexConfig->removeAll();
        $indexConfig->setData($baseKeys);
        if (!$storage->save($indexConfig)) {
            throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $indexConfig->getName());
        }
    }

    /**
     * Get dictionary index name
     * @param string $dictionary
     * @return string
     */
    public function getIndexName($dictionary = ''): string
    {
        return str_replace('/', '_', $dictionary) . '_index.php';
    }

    /**
     * Get dictionary_index
     * @param string $dictionary
     * @return array<int|string,mixed>|false
     */
    public function getIndex(string $dictionary = '')
    {
        $subPackage = basename($dictionary);
        $indexName = $this->getIndexName($subPackage);

        $indexFile = $this->langService->getStorage()->getPath($indexName);

        if (!file_exists((string)$indexFile)) {
            return false;
        }

        $data = include $indexFile;

        if (!is_array($data)) {
            return false;
        }

        return $data;
    }

    /**
     * Update index content
     * @param array<int|string,mixed> $data
     * @param string $dictionary - optional
     * @return void
     * @throws Exception
     */
    public function updateIndex(array $data, string $dictionary): void
    {
        $subPackage = basename($dictionary);
        $indexName = $this->getIndexName($subPackage);

        $writePath = $this->langService->getStorage()->getWrite();

        if (!file_exists($writePath . $indexName)) {
            if (!\Dvelum\Utils::exportArray($writePath . $indexName, [])) {
                throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $writePath . $indexName);
            }
        }
        $storage = $this->langService->getStorage();
        $indexConfig = $storage->get($indexName);
        $indexConfig->removeAll();
        $indexConfig->setData($data);
        if (!$storage->save($indexConfig)) {
            throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $writePath . $indexName);
        }
    }

    /**
     * Get localization config
     * @param string $dictionary
     * @return array{id:int|string,key:string,title:string,sync:bool}[]
     * @phpstan-return array<int,array>
     */
    public function getLocalization(string $dictionary): array
    {
        $dictionaryData = $this->langService->getStorage()->get($dictionary . '.php')->__toArray();

        if (strpos($dictionary, '/') !== false) {
            $index = $this->getIndex($dictionary);
        } else {
            $index = $this->getIndex();
        }

        if (!is_array($index)) {
            return [];
        }

        $keys = array_keys($dictionaryData);
        $newKeys = array_diff($keys, $index);
        $result = [];

        foreach ($index as $dKey) {
            $value = '';
            $sync = true;
            if (isset($dictionaryData[$dKey])) {
                $value = $dictionaryData[$dKey];
            } else {
                $sync = false;
            }

            $result[] = array('id' => $dKey, 'key' => $dKey, 'title' => $value, 'sync' => $sync);
        }

        if (!empty($newKeys)) {
            foreach ($newKeys as $key) {
                $result[] = array('id' => $key, 'key' => $key, 'title' => $dictionaryData[$key], 'sync' => true);
            }
        }
        return $result;
    }

    /**
     * Add key to localization index
     * @param string $key
     * @param string $dictionary
     * @return void
     * @throws \Exception
     */
    public function addToIndex(string $key, string $dictionary = ''): void
    {
        /**
         * @var array<int|string,mixed> $index
         */
        $index = $this->getIndex($dictionary);
        if (empty($index)) {
            $index = [];
        }

        if (!in_array($key, $index, true)) {
            $index[] = $key;
        }

        $this->updateIndex($index, $dictionary);
    }

    /**
     * Remove key from localization index
     * @param string $key
     * @param string $dictionary
     * @return void
     * @throws \Exception
     */
    public function removeFromIndex($key, $dictionary = ''): void
    {
        /**
         * @var array<int|string,mixed> $index
         */
        $index = $this->getIndex($dictionary);
        if (empty($index)) {
            $index = [];
        }

        if (!in_array($key, $index, true)) {
            return;
        }
        foreach ($index as $k => $v) {
            if ($v === $key) {
                unset($index[$k]);
            }
        }
        $this->updateIndex($index, $dictionary);
    }

    /**
     * Add dictionary record
     * @param string $dictionary
     * @param string $key
     * @param string[] $langs
     * @return void
     * @throws Exception
     */
    public function addRecord($dictionary, $key, array $langs): void
    {
        $isSub = false;
        $dictionaryName = $dictionary;

        if (strpos($dictionary, '/') !== false) {
            $tmp = explode('/', $dictionary);
            $dictionaryName = $tmp[1];
            $isSub = true;
        }

        if ($isSub) {
            $index = $this->getIndex($dictionary);
        } else {
            $index = $this->getIndex();
        }

        /**
         * @var array<int|string,mixed> $index
         */
        if (empty($index)) {
            $index = [];
        }

        // add index for dictionary key
        if (!in_array($key, $index, true)) {
            if ($isSub) {
                $this->addToIndex($key, $dictionary);
            } else {
                $this->addToIndex($key);
            }
        }

        $writePath = $this->langService->getStorage()->getWrite();
        $storage = $this->langService->getStorage();
        if (!$isSub) {
            foreach ($langs as $langName => $value) {
                $langFile = $writePath . $langName . '.php';
                try {
                    $langConfig = $storage->get($langName . '.php');
                } catch (\Throwable $e) {
                    throw new Exception($this->lang->get('CANT_LOAD') . ' ' . $langName);
                }
                $langConfig->set($key, $value);
                if (!$storage->save($langConfig)) {
                    throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $langFile);
                }
            }
        } else {
            foreach ($langs as $langName => $value) {
                $langFile = $writePath . $langName . '/' . $dictionaryName . '.php';
                try {
                    $langConfig = $this->langService->getStorage()->get($langName . '/' . $dictionaryName . '.php');
                } catch (\Throwable $e) {
                    throw new Exception($this->lang->get('CANT_LOAD') . ' ' . $langName . '/' . $dictionaryName);
                }
                $langConfig->set($key, $value);
                if (!$storage->save($langConfig)) {
                    throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $langFile);
                }
            }
        }
    }

    /**
     * Check if file exists and writable
     * @param string $file
     * @return bool
     */
    protected function checkCanEdit(string $file): bool
    {
        if (file_exists($file) && is_writable($file)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Remove key from localizations
     * @param string $dictionary
     * @param string $key
     * @return void
     * @throws Exception
     */
    public function removeRecord(string $dictionary, string $key): void
    {
        $isSub = false;

        if (strpos($dictionary, '/') !== false) {
            $tmp = explode('/', $dictionary);
            $dictionaryName = $tmp[1];
            $isSub = true;
        } else {
            $dictionaryName = $dictionary;
        }

        if ($isSub) {
            $this->removeFromIndex($key, $dictionary);
        } else {
            $this->removeFromIndex($key);
        }

        $mainLangs = $this->getLangs(true);

        $storage = $this->langService->getStorage();
        $writePath = $storage->getWrite();

        if (!$isSub) {
            foreach ($mainLangs as $langName) {
                $langFile = $writePath . $langName . '.php';
                try {
                    $langConfig = $storage->get($langName . '.php');
                } catch (\Throwable $e) {
                    throw new Exception($this->lang->get('CANT_LOAD') . ' ' . $langName);
                }

                $langConfig->remove($key);
                if (!$storage->save($langConfig)) {
                    throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $langFile);
                }
            }
        } else {
            foreach ($mainLangs as $langName) {
                $langFile = $writePath . $langName . '/' . $dictionaryName . '.php';
                try {
                    $langConfig = $storage->get($langName . '/' . $dictionaryName . '.php');
                } catch (\Throwable $e) {
                    throw new Exception($this->lang->get('CANT_LOAD') . ' ' . $langName . '/' . $dictionaryName);
                }

                $langConfig->remove($key);
                if (!$storage->save($langConfig)) {
                    throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $langFile);
                }
            }
        }
    }

    /**
     * Update localization records
     * @param string $dictionary
     * @param array<int|string,mixed> $data
     * @return void
     * @throws Exception
     */
    public function updateRecords(string $dictionary, array $data): void
    {
        $writePath = $this->langService->getStorage()->getWrite() . $dictionary . '.php';

        $langConfig = $this->langService->getStorage()->get($dictionary . '.php');

        foreach ($data as $rec) {
            $langConfig->set($rec['id'], $rec['title']);
        }

        if (!$this->langService->getStorage()->save($langConfig)) {
            throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $writePath);
        }
    }

    /**
     * Check if dictionary exists (only sub dictionaries not languages)
     * @param string $name
     * @return bool
     */
    public function dictionaryExists(string $name): bool
    {
        $list = $this->getSubDictionaries();

        if (in_array($name, $list, true)) {
            return true;
        }

        return false;
    }

    /**
     * Create sub dictionary
     * @param string $name
     * @return void
     * @throws Exception
     */
    public function createDictionary(string $name): void
    {
        $writePath = $this->langService->getStorage()->getWrite();
        $indexPath = $writePath . $this->getIndexName($name);

        $indexLocation = dirname($indexPath);

        if (!file_exists($indexLocation) && !@mkdir($indexLocation, 0775, true)) {
            throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $indexLocation);
        }

        if (!\Dvelum\Utils::exportArray($indexPath, [])) {
            throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $indexPath);
        }

        $langs = $this->getLangs(true);

        foreach ($langs as $lang) {
            $fileLocation = $writePath . $lang;

            if (!file_exists($fileLocation) && !@mkdir($fileLocation, 0775, true)) {
                throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $fileLocation);
            }

            $filePath = $fileLocation . '/' . $name . '.php';

            if (!\Dvelum\Utils::exportArray($filePath, [])) {
                throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $filePath);
            }
        }
    }

    /**
     * Create JS lang files
     * @return void
     * @throw Exception
     */
    public function compileLangFiles(): void
    {
        $jsPath = $this->appConfig->get('js_lang_path');
        ;
        $langs = $this->getLangs(false);

        /**
         * @var Lang $langService
         */
        $langService = $this->langService;

        $exceptDirs = ['objects', 'modules'];

        foreach ($langs as $lang) {
            $name = $lang;

            $langService->addLoader($name, $lang . '.php', Factory::FILE_ARRAY);

            $filePath = $jsPath . $lang . '.js';

            $dir = dirname($lang);

            if (in_array(basename($dir), $exceptDirs, true)) {
                continue;
            }

            if (!empty($dir) && $dir !== '.' && !is_dir($jsPath . '/' . $dir)) {
                mkdir($jsPath . '/' . $dir, 0755, true);
            }

            $varName = basename($name) . 'Lang';

            if (strpos($name, '/') === false) {
                $varName = 'appLang';
            }

            if (!@file_put_contents($filePath, 'var ' . $varName . ' = ' . Lang::lang($name)->getJsObject() . ';')) {
                throw new Exception($this->lang->get('CANT_WRITE_FS') . ' ' . $filePath);
            }
        }
    }
}
