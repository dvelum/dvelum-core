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

namespace Dvelum\Store;

/**
 * Store Factory
 * @author Kirill Egorov 2010
 */
class Factory
{
    public const LOCAL = 1;
    public const SESSION = 2;

    /**
     * @var array<int,array<string,AdapterInterface>>
     */
    protected static array $instances = [];

    /**
     * Store factory
     * @param int $type - const
     * @param string $name
     * @return AdapterInterface
     * @throws \Exception
     */
    public static function get(int $type = self::LOCAL, string $name = 'default'): AdapterInterface
    {
        switch ($type) {
            case self::LOCAL:
                if (!isset(self::$instances[$type][$name])) {
                    self::$instances[$type][$name] = new \Dvelum\Store\Local($name);
                }
                return self::$instances[$type][$name];
            case self::SESSION:
                if (!isset(self::$instances[$type][$name])) {
                    self::$instances[$type][$name] = new \Dvelum\Store\Session($name);
                }
                return self::$instances[$type][$name];
            default:
                throw new \Exception('Undefined type' . $type);
        }
    }
}
