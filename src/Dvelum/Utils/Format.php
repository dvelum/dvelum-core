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

namespace Dvelum\Utils;

use Dvelum\Tree\ArrayTree as Tree;

class Format
{
    /**
     * Convert files list into Tree structure
     * @param array<mixed,mixed> $data
     * @return Tree
     */
    public static function fileListToTree(array $data): Tree
    {
        $tree = new Tree();
        foreach ($data as $k => $v) {
            $tmp = explode('/', substr($v, 2));
            for ($i = 0, $s = sizeof($tmp); $i < $s; $i++) {
                if ($i == 0) {
                    $id = $tmp[0];
                    $par = 0;
                } else {
                    $id = implode('/', array_slice($tmp, 0, $i + 1));
                    $par = implode('/', array_slice($tmp, 0, $i));
                }
                $tree->addItem($id, $par, $tmp[$i]);
            }
        }
        return $tree;
    }

    /**
     * Format time
     * @param int $difference
     * @return string
     */
    public static function formatTime(int $difference): string
    {
        $days = floor($difference / 86400);
        $difference = $difference % 86400;
        $hours = floor($difference / 3600);
        $difference = $difference % 3600;
        $minutes = floor($difference / 60);
        $difference = $difference % 60;
        $seconds = floor($difference);
        if ($minutes == 60) {
            $hours = $hours + 1;
            $minutes = 0;
        }
        $s = '';

        if ($days > 0) {
            $s .= $days . ' days ';
        }

        $s .= str_pad((string)$hours, 2, '0', STR_PAD_LEFT) .
            ':' .
            str_pad((string)$minutes, 2, '0', STR_PAD_LEFT) .
            ':' .
            str_pad((string)$seconds, 2, '0', STR_PAD_LEFT);
        return $s;
    }

    /**
     * Format file size in user friendly
     * @param int $size
     * @return string
     */
    public static function formatFileSize(int $size): string
    {
        /*
         * 1024 * 1024 * 1024  - Gb
        */
        if ($size > 1073741824) {
            return number_format($size / 1073741824, 1) . ' Gb';
        }
        /*
         * 1024 * 1024 - Mb
        */
        if ($size > 1048576) {
            return number_format($size / 1048576, 1) . ' Mb';
        }
        /*
         * 1024  - Kb
        */
        if ($size > 1024) {
            return number_format($size / 1024, 1) . ' Kb';
        }
        return $size . ' B';
    }
}
