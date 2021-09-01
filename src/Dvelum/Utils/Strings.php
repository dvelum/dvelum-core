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

class Strings
{
    /**
     * Get random string
     * @param int $length - string length
     * @return string
     */
    public static function getRandomString(int $length): string
    {
        $string = '';
        $symbols = array(
            'q',
            'w',
            'e',
            'r',
            't',
            'y',
            'u',
            'i',
            'o',
            'p',
            'a',
            's',
            'd',
            'f',
            'g',
            'h',
            'j',
            'k',
            'l',
            'z',
            'x',
            'c',
            'v',
            'b',
            'n',
            'm',
            1,
            2,
            3,
            4,
            5,
            6,
            7,
            8,
            9,
            0,
            'Q',
            'W',
            'E',
            'R',
            'T',
            'Y',
            'U',
            'I',
            'O',
            'P',
            'A',
            'S',
            'D',
            'F',
            'G',
            'H',
            'J',
            'K',
            'L',
            'Z',
            'X',
            'C',
            'V',
            'B',
            'N',
            'M'
        );
        $size = sizeof($symbols) - 1;
        while ($length) {
            $string .= $symbols[mt_rand(0, $size)];
            --$length;
        }

        return $string;
    }

    /**
     * @return string[]
     */
    public static function alphabetEn(): array
    {
        return [
            'a',
            'b',
            'c',
            'd',
            'e',
            'f',
            'g',
            'h',
            'i',
            'j',
            'k',
            'l',
            'm',
            'n',
            'o',
            'p',
            'q',
            'r',
            's',
            't',
            'u',
            'v',
            'w',
            'x',
            'y',
            'z'
        ];
    }

    /**
     * Normalize string to class name
     * @param string $str
     * @param bool $useNamespace
     * @return string
     */
    public static function classFromString(string $str, bool $useNamespace = false): string
    {
        if ($useNamespace) {
            $sep = '\\';
        } else {
            $sep = '_';
        }
        $parts = explode($sep, $str);
        $parts = array_map('ucfirst', $parts);
        return implode($sep, $parts);
    }

    /**
     * Normalize class name
     * @param string $name
     * @param bool $useNamespace
     * @return string
     */
    public static function formatClassName($name, bool $useNamespace = false): string
    {
        if ($useNamespace) {
            $sep = '\\';
        } else {
            $sep = '_';
        }

        $nameParts = explode('\\', str_replace('_', '\\', $name));
        $nameParts = array_map('ucfirst', $nameParts);
        return implode($sep, $nameParts);
    }

    /**
     * Add lines indent
     * @param string $string
     * @param integer $tabsCount , optional, default = 1
     * @param string $indent , optional, default = "\t"
     * @param bool $ignoreFirstLine
     * @return string
     */
    public static function addIndent(
        string $string,
        int $tabsCount = 1,
        string $indent = "\t",
        bool $ignoreFirstLine = false
    ): string {
        $indent = str_repeat("\t", $tabsCount);
        if ($ignoreFirstLine) {
            return str_replace("\n", "\n" . $indent, $string);
        } else {
            return $indent . str_replace("\n", "\n" . $indent, $string);
        }
    }

    /**
     * Limit text
     * @param string $string
     * @param int $maxLength
     * @return  string
     */
    public static function limitText(string $string, int $maxLength): string
    {
        $strlen = strlen($string);
        if ($strlen <= $maxLength) {
            return $string;
        }

        $string = substr($string, 0, $maxLength);
        $wordStart = strrpos($string, ' ');

        if ($wordStart) {
            $string = substr($string, 0, $wordStart);
        }

        $string .= '...';
        return $string;
    }
}
