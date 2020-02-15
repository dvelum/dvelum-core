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

use \Dvelum\Tree\ArrayTree as Tree;

/**
 * System Utils class Do not include into packages!
 * @author Kirill Yegorov 2011
 * @package Dvelum
 */
class Utils
{
    /**
     * Create an array from another array using field as key
     *
     * @param string $key
     * @param array $data
     * @throws \Exception
     * @return array
     */
    static public function rekey(string $key, array $data): array
    {
        $result = array();

        foreach ($data as $v) {
            if (!isset($v[$key])) {
                throw new \Exception('Invalid key');
            }

            $result[$v[$key]] = $v;
        }
        return $result;
    }

    /**
     * Collect data from result set
     * @param string $keyField
     * @param string $valueField
     * @param array $data
     * @throws \Exception
     * @return array
     */
    static public function collectData(string $keyField, string $valueField, array $data): array
    {
        $result = [];
        foreach ($data as $v) {
            if (!isset($v[$keyField]) || !isset($v[$valueField])) {
                throw new \Exception('Invalid key');
            }
            $result[$v[$keyField]] = $v[$valueField];
        }
        return $result;
    }

    /**
     * Fetch array column
     * @param string $key
     * @param array $data
     * @throws \Exception
     * @return array
     */
    static public function fetchCol(string $key, array $data): array
    {
        $result = [];

        if (empty($data)) {
            return [];
        }

        foreach ($data as $v) {
            if(!is_object($v) || $v instanceof \ArrayAccess){
                $result[] = $v[$key];
            } else {
                $result[] = $v->{$key};
            }
        }
        return $result;
    }

    /**
     * Group array by column, used for db results sorting
     * @param string $key
     * @param array $data
     * @throws \Exception
     * @return array
     */
    static public function groupByKey(string $key, array $data): array
    {
        $result = [];

        if (empty($data)) {
            return [];
        }

        foreach ($data as $v) {
            if (!isset($v[$key])) {
                throw new \Exception('Invalid key ' . $key);
            }

            $result[$v[$key]][] = $v;
        }
        return $result;
    }
    /**
     * Format file size in user friendly
     * @param int $size
     * @return string
     */
    static public function formatFileSize(int $size): string
    {
        return Utils\Format::formatFileSize($size);
    }

    /**
     * Format time
     * @param int $difference
     * @return string
     */
    static public function formatTime(int $difference): string
    {
        return Utils\Format::formatTime($difference);
    }

    /**
     * Export php array into the file
     * This function may return Boolean FALSE,
     * but may also return a non-Boolean value which evaluates to FALSE.
     *
     * Please read the section on Booleans for more information.
     * Use the === operator for testing the return value of this function.
     *
     * @param string $file
     * @param array $data
     * @return bool
     */
    static public function exportArray(string $file, array $data): bool
    {
        try {
            file_put_contents($file, '<?php return ' . var_export($data, true) . '; ');
            @chmod($file, 0775);
        } catch (\Throwable $e) {
            return false;
        }
        return true;
    }

    /**
     * Export php code
     * This function may return Boolean FALSE,
     * but may also return a non-Boolean value which evaluates to FALSE.
     *
     * Please read the section on Booleans for more information.
     * Use the === operator for testing the return value of this function.
     *
     * @param string $file
     * @param string $string
     * @return bool
     */
    static public function exportCode(string $file, string $string): bool
    {
        try {
            file_put_contents($file, '<?php ' . $string);
            return true;
        } catch (\Error $e) {
            return false;
        }
    }

    /**
     * Create class name from file path
     * @param string $path
     * @param bool $check
     * @return null|string
     */
    static public function classFromPath(string $path, bool $check = false): ?string
    {
        return Utils\Fs::classFromPath($path, $check);
    }

    /**
     * Create path for cache file
     * @param string $basePath
     * @param string $fileName
     * @return string
     */
    static public function createCachePath(string $basePath, string $fileName): string
    {
        return Utils\Fs::createCachePath($basePath, $fileName);
    }

    /**
     * Convert files list into Tree structure
     * @param array $data
     * @return Tree
     */
    static public function fileListToTree(array $data): Tree
    {
        return Utils\Format::fileListToTree($data);
    }

    /**
     * Get random string
     * @param integer $length - string length
     * @return string
     */
    static function getRandomString($length): string
    {
        return Utils\Strings::getRandomString($length);
    }

    /**
     * Check if operation system is windows
     * @return boolean
     */
    static function isWindows(): bool
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get user IP address
     * @return string
     */
    static public function getClientIp(): string
    {
        $ip = 'Unknown';

        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        } elseif (isset($_ENV['HTTP_CLIENT_IP']) && strcasecmp($_ENV['HTTP_CLIENT_IP'], 'unknown') !== 0) {
            $ip = $_ENV['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], 'unknown') !== 0) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_ENV['HTTP_X_FORWARDED_FOR']) && strcasecmp($_ENV['HTTP_X_FORWARDED_FOR'], 'unknown') !== 0) {
            $ip = $_ENV['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'],
                'unknown') !== 0
        ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_ENV['REMOTE_ADDR']) && strcasecmp($_ENV['REMOTE_ADDR'], 'unknown') !== 0) {
            $ip = $_ENV['REMOTE_ADDR'];
        } elseif (isset($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown') !== 0) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        if (stristr($ip, ",")) {
            $ip_arr = explode(",", $ip);
            $ip = $ip_arr[0];
        }
        return $ip;
    }

    /**
     * Sort array list by sub array field
     * Faster then uasort
     * @param array $data
     * @param string $field
     * @param bool $reverse
     * @return array
     */
    static public function sortByField(array $data, string $field, bool $reverse = false): array
    {
        $index = [];

        foreach ($data as $id => $item) {
            $index[$id] = $item[$field];
        }

        if($reverse){
            arsort($index);
        }else{
            asort($index);
        }

        $result = [];

        foreach ($index as $id => $value) {
            $result[] = $data[$id];
        }

        return $result;
    }

    /**
     * Sort an array of objects by object property
     * @param array $list
     * @param string $property
     * @return  array
     */
    static public function sortByProperty(array $list, string $property): array
    {
        $index = [];

        foreach ($list as $id => $item) {
            $index[$id] = $item->{$property};
        }
        asort($index);

        $result = [];

        foreach ($index as $id => $value) {
            $result[] = $list[$id];
        }

        return $result;
    }

    /**
     * Transfer an array to a list of integers for inserting into an SQL-query form,
     * is used to improve the performance of Zend_Select queries setup
     * join values by ","
     * @param array $ids
     * @return string
     */
    static public function listIntegers(array $ids): string
    {
        return implode(',', array_map('intval', array_unique($ids)));
    }

    /**
     * Round float up
     * @param float|int $number
     * @param int $precision - expected precision
     * @return float|int
     */
    static public function roundUp($number, int $precision)
    {
        $precision++;
        $fig = (int) str_pad('1', $precision, '0');
        return (ceil($number * $fig) / $fig);
    }

    /**
     * @param array $array1
     * @param array $array2
     * @return array
     */
    static public function array_diff_assoc_recursive($array1, $array2) : array
    {
        foreach($array1 as $key => $value)
        {
            if(is_array($value))
            {
                if(!isset($array2[$key]))
                {
                    $difference[$key] = $value;
                }
                elseif(!is_array($array2[$key]))
                {
                    $difference[$key] = $value;
                }
                else
                {
                    $new_diff = self::array_diff_assoc_recursive($value, $array2[$key]);
                    if($new_diff != FALSE)
                    {
                        $difference[$key] = $new_diff;
                    }
                }
            }
            elseif(!isset($array2[$key]) || $array2[$key] != $value)
            {
                $difference[$key] = $value;
            }
        }
        return !isset($difference) ? [] : $difference;
    }
}