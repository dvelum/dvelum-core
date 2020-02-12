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

class Console
{
    const COLOR_BLACK = 'black';
    const COLOR_DARK_GRAY = 'dark_gray';
    const COLOR_BLUE = 'blue';
    const COLOR_LIGHT_BLUE = 'light_blue';
    const COLOR_GREEN = 'green';
    const COLOR_LIGHT_GREEN = 'light_green';
    const COLOR_CYAN = 'cyan';
    const COLOR_LIGHT_CYAN = 'light_cyan';
    const COLOR_RED = 'red';
    const COLOR_LIGHT_RED = 'light_red';
    const COLOR_PURPLE = 'purple';
    const COLOR_LIGHT_PURPULE = 'light_purple';
    const COLOR_BROWN = 'brown';
    const COLOR_YELLOW = 'yellow';
    const COLOR_LIGHT_GRAY = 'light_gray';
    const COLOR_WHITE = 'white';

    /**
     * @var array
     */
    static protected $fgColors = [
        'black' => '0;30',
        'dark_gray' => '1;30',
        'blue' => '0;34',
        'light_blue' => '1;34',
        'green' => '0;32',
        'light_green' => '1;32',
        'cyan' => '0;36',
        'light_cyan' => '1;36',
        'red' => '0;31',
        'light_red' => '1;31',
        'purple' => '0;35',
        'light_purple' => '1;35',
        'brown' => '0;33',
        'yellow' => '1;33',
        'light_gray' => '0;37',
        'white' => '1;37'
    ];
    /**
     * @var array
     */
    static protected $bgColors = [
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'light_gray' => '47',
    ];

    /**
     * Returns colored string
     * @param string $string
     * @param null|string $foreground
     * @param null|string $background
     * @return string
     */
    static public function getColored(string $string, ?string $foreground = null, ?string $background = null): string
    {
        $result = "";
        // Check if given foreground color found
        if (isset(self::$fgColors[$foreground])) {
            $result .= "\033[" . self::$fgColors[$foreground] . "m ";
        }
        // Check if given background color found
        if (isset(self::$bgColors[$background])) {
            $result .= "\033[" . self::$bgColors[$background] . "m ";
        }
        // Add string and end coloring
        $result .= $string . " \033[0m ";
        return $result;
    }
}