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

namespace Dvelum;

/**
 * Filesystem wrapper
 * @author Kirill A Egorov 2011
 * @license GPLv3
 * @package Dvelum
 * @todo refactor return types and arguments into 7.1
 */
class File
{
    public const FILES_DIRS = 0;
    public const FILES_ONLY = 1;
    public const DIRS_ONLY = 2;

    /**
     * Default Separator in file paths
     * Can be disabled by  setDirectorySeparator (false)
     * @var mixed string | false
     */
    protected static $directorySeparator = '/';

    /**
     * Set directory separator for output data
     * @param mixed $sep string or false
     * @return void
     */
    public static function setDirectorySeparator($sep): void
    {
        self::$directorySeparator = $sep;
    }

    /**
     * Get file extension
     * @param string $name
     * @return string
     */
    public static function getExt(string $name): string
    {
        $ext = strrchr(strtolower($name), '.');
        if (empty($ext)) {
            $ext = '';
        }
        return $ext;
    }

    /**
     * Add path separator to the end of string
     * @param string $path
     * @return string
     */
    public static function fillEndSep(string $path): string
    {
        $length = strlen($path);
        if (!$length || $path[$length - 1] !== self::$directorySeparator) {
            $path .= self::$directorySeparator;
        }

        return $path;
    }

    /**
     * Get file list
     * @param string $path
     * @param array<int,string>|false $filter - optional  array of file extensions to search for
     * @param bool $recursive - optional    use recursion (default true)
     * @param int $type - optional File::Dirs_Only | File::Files_Dirs | File::Files_Only (default File::Files_Dirs)
     * @param int $mode - optional RecursiveIteratorIterator::SELF_FIRST | RecursiveIteratorIterator::CHILD_FIRST
     * (default RecursiveIteratorIterator::SELF_FIRST)
     * @return array<int,string>
     * @throws \Exception
     */
    public static function scanFiles(
        $path,
        $filter = false,
        $recursive = true,
        $type = File::FILES_DIRS,
        $mode = \RecursiveIteratorIterator::SELF_FIRST
    ) {
        $path = self::fillEndSep($path);
        $files = array();
        $collectDirs = false;
        $collectFiles = false;

        switch ($type) {
            case self::FILES_ONLY:
                $mode = \RecursiveIteratorIterator::LEAVES_ONLY;
                $collectFiles = true;
                break;
            case self::DIRS_ONLY:
                $collectDirs = true;
                break;
            case self::FILES_DIRS:
                $collectDirs = true;
                $collectFiles = true;
                break;
        }
        try {
            if ($recursive) {
                $dirIterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                    $mode
                );
            } else {
                $dirIterator = new \FilesystemIterator($path, \FilesystemIterator::SKIP_DOTS);
            }
        } catch (\Exception $e) {
            throw new \Exception('You tried to read nonexistent dir: ' . $path);
        }

        $changeSep = false;

        if (self::$directorySeparator && self::$directorySeparator !== DIRECTORY_SEPARATOR) {
            $changeSep = self::$directorySeparator;
        }

        foreach ($dirIterator as $name => $object) {
            $add = false;
            $isDir = $object->isDir();

            if (($isDir && $collectDirs) || (!$isDir && $collectFiles)) {
                $add = true;
            }

            if (!empty($filter)) {
                if (!$isDir && !in_array(self::getExt($name), $filter, true)) {
                    $add = false;
                }
            }

            if ($add) {
                if ($changeSep) {
                    $name = str_replace(DIRECTORY_SEPARATOR, $changeSep, $name);
                    $name = str_replace($changeSep . $changeSep, $changeSep, $name);
                }
                $files[] = $name;
            }
        }
        unset($dirIterator);
        return $files;
    }

    /**
     * Adds given files to existing archive $fileName or create a new archive by the same path
     * @param string $fileName - path to file
     * @param mixed $files array or string
     * @param string $localRoot optional
     * @return bool
     */
    public static function zipFiles($fileName, $files, $localRoot = '')
    {
        if (substr($fileName, -4) !== '.zip') {
            $fileName .= '.zip';
        }

        // delete existing file
        if (file_exists($fileName)) {
            unlink($fileName);
        }

        $zip = new \ZipArchive();

        /**
         * ZIPARCHIVE::CREATE (integer)
         * Create the archive if it does not exist.
         */
        if ($zip->open($fileName, \ZipArchive::CREATE) !== true) {
            return false;
        }

        if (is_string($files)) {
            $files = array($files);
        }

        if (!empty($files)) {
            foreach ($files as $file) {
                if (is_dir($file)) {
                    if ($localRoot !== '') {
                        $zip->addEmptyDir(str_replace($localRoot, '', $file));
                    } else {
                        $zip->addEmptyDir($file);
                    }
                    continue;
                }

                if ($localRoot !== '') {
                    $zip->addFile($file, str_replace($localRoot, '', $file));
                } else {
                    $zip->addFile($file);
                }
            }
        }
        return $zip->close();
    }

    /**
     * Extract all files
     * @param string $source
     * @param string $destination
     * @param array<string>|string|false $fileEntries - optional - The entries to extract.
     * It accepts either a single entry name or an array of names.
     * @return bool
     */
    public static function unzipFiles(string $source, string $destination, $fileEntries = false)
    {
        $zip = new \ZipArchive();

        if ($zip->open($source) !== true) {
            return false;
        }

        $result = true;
        if (!empty($fileEntries)) {
            if (!$zip->extractTo($destination, $fileEntries)) {
                $result = false;
            }
        } else {
            if (!$zip->extractTo($destination)) {
                $result = false;
            }
        }
        $zip->close();
        return $result;
    }

    /**
     * Get Archive items list
     * @param string $source
     * @return array<int,string>
     */
    public static function getZipItemsList(string $source): array
    {
        $zip = new \ZipArchive();

        if ($zip->open($source) !== true) {
            return [];
        }

        $zipSize = $zip->numFiles - 1;

        $itemsList = [];

        while ($zipSize >= 0) {
            $item = $zip->getNameIndex((int)$zipSize);
            if ($item !== false) {
                $itemsList[] = $item;
            }
            --$zipSize;
        }
        return $itemsList;
    }

    /**
     * Recursively remove files and dirs from given $pathname
     * @param string $pathname
     * @param bool $removeParentDir
     * @return bool
     */
    public static function rmdirRecursive(string $pathname, bool $removeParentDir = false): bool
    {
        $filesDirs = self::scanFiles($pathname, false, true, File::FILES_DIRS, \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($filesDirs as $v) {
            if (is_dir($v)) {
                if (!rmdir($v)) {
                    return false;
                }
            } elseif (is_file($v) || is_link($v)) {
                if (!unlink($v)) {
                    return false;
                }
            } else {
                return false;
            }
        }

        if ($removeParentDir) {
            if (!rmdir($pathname)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Copy directory contents
     * @param string $source
     * @param string $dest
     * @return bool
     */
    public static function copyDir(string $source, string $dest): bool
    {
        if (!is_dir($dest)) {
            if (!@mkdir($dest, 0755, true)) {
                return false;
            }
        }

        /**
         * @var \RecursiveDirectoryIterator $iterator
         */
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $source,
                \RecursiveDirectoryIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        try {
            foreach ($iterator as $item) {
                /**
                 * @var \SplFileInfo $item
                 */
                if ($item->isDir()) {
                    $subDir = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
                    if (!is_dir($subDir) && !mkdir($subDir, 0755)) {
                        return false;
                    }
                } else {
                    if (!copy($item->__toString(), $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName())) {
                        return false;
                    }
                }
            }
        } catch (\Error $e) {
            return false;
        }

        return true;
    }

    /**
     * Copies files and dirs to $destPath
     * @param string $destPath
     * @param mixed $files
     * @param string $localRoot - optional
     * @return bool
     */
    public static function copyFiles(string $destPath, $files, $localRoot = ''): bool
    {
        if (!file_exists($destPath)) {
            if (!mkdir($destPath, 0775)) {
                return false;
            }
        }

        if (is_string($files)) {
            $files = array($files);
        }

        if (empty($files)) {
            return false;
        }

        foreach ($files as $path) {
            $dest = $destPath . str_replace($localRoot, '', $path);
            if (is_dir($path)) {
                if (!file_exists($dest)) {
                    if (!mkdir($dest, 0775, true)) {
                        return false;
                    }
                }
            } else {
                $dir = dirname($dest);
                if (!file_exists($dir)) {
                    if (!mkdir($dir, 0775, true)) {
                        return false;
                    }
                }
                if (!copy($path, $dest)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Find the last existing dir by $path
     * @param string $path
     * @return string | bool
     */
    public static function getExistingDirByPath($path)
    {
        if (is_file($path)) {
            return dirname($path);
        }

        if (is_dir($path)) {
            return $path;
        }

        $pathArr = explode('/', $path);
        for ($i = sizeof($pathArr) - 1; $i > 0; $i--) {
            unset($pathArr[$i]);

            $cur = implode('/', $pathArr);

            if (is_dir($cur)) {
                return $cur;
            }
        }
        return false;
    }

    /**
     * Checks writing permissions for files.
     * Returns array with paths (wich is not writable) or true on success
     * @param array<string> $files
     * @return true|array<string>
     */
    public static function checkWritePermission(array $files)
    {
        $cantWrite = array();
        foreach ($files as $path) {
            $path = (string)$path;
            if (is_file($path)) {
                if (!is_writable($path)) {
                    $cantWrite[] = $path;
                }
                continue;
            }

            $dir = self::getExistingDirByPath($path);
            if (is_string($dir) && !is_writable($dir)) {
                $cantWrite[] = $path;
            }
        }

        if (empty($cantWrite)) {
            return true;
        }

        return $cantWrite;
    }
}
