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

namespace Dvelum\Utils;

use Dvelum\File;

class Fs
{
    /**
     * Create class name from file path
     * @param string $path
     * @param bool $check
     * @return null|string
     */
    public static function classFromPath(string $path, bool $check = false): ?string
    {
        // windows path hack
        $path = str_replace('\\', '/', $path);
        $path = str_replace('//', '/', $path);

        if (File::getExt($path) !== '.php') {
            return null;
        }

        $path = substr($path, 0, -4);

        if (strpos($path, ('../')) === 0) {
            $path = substr($path, 3);
        } elseif (strpos($path, ('./')) === 0) {
            $path = substr($path, 2);
        } elseif (strpos($path, '/') === 0) {
            $path = substr($path, 1);
        }

        $result = implode('_', array_map('ucfirst', explode('/', $path)));

        if ($check && !class_exists($result)) {
            $result = '\\' . str_replace('_', '\\', $result);
            if (!class_exists($result)) {
                return null;
            }
        }
        return $result;
    }


    /**
     * Create path for cache file
     * @param string $basePath
     * @param string $fileName
     * @return string
     */
    public static function createCachePath(string $basePath, string $fileName): string
    {
        $extension = File::getExt($fileName);

        $str = md5($fileName);
        $len = strlen($str);
        $path = '';
        $count = 0;
        $parts = 0;
        for ($i = 0; $i < $len; $i++) {
            if ($count == 4) {
                $path .= '/';
                $count = 0;
                $parts++;
            }
            if ($parts == 4) {
                break;
            }
            $path .= $str[$i];
            $count++;
        }
        $path = $basePath . $path;

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path . $str . $extension;
    }
}
