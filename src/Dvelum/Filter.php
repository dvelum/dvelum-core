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

class Filter
{
    const FILTER_ARRAY = 'array';
    const FILTER_BOOLEAN = 'bool';
    const FILTER_INTEGER = 'int';
    const FILTER_FLOAT = 'float';
    const FILTER_STRING = 'str';
    const FILTER_CLEANED_STR = 'cleaned_string';
    const FILTER_EMAIL = 'email';
    const FILTER_ALPHANUM = 'alphanum';
    const FILTER_NUM = 'num';
    const FILTER_ALPHA = 'alpha';
    const FILTER_LOGIN = 'login';
    const FILTER_PAGECODE = 'pagecode';
    const FILTER_RAW = 'raw';
    const FILTER_URL = 'url';

    protected static $autoConvertFloatSeparator = true;

    /**
     * String cleanup
     * @param string $string
     * @return string
     */
    static public function filterString(string $string) : string
    {
        return trim(strip_tags($string));
    }

    /**
     * Filter variable
     * @param string $filter
     * @param mixed $value
     * @return mixed
     */
    static public function filterValue(string $filter, $value)
    {
        $filter = strtolower($filter);
        switch ($filter) {
            case 'array' :
                if (!is_array($value)) {
                    if(!empty($value)){
                        $value = [$value];
                    }else{
                        $value = [];
                    }
                }
                break;
            case 'bool' :
            case 'boolean' :
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;
            case 'int' :
            case 'integer' :
                $value = intval($value);
                break;
            case 'float' :
            case 'decimal' :
            case 'number' :
                $value = filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT,
                    FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND);
                if (is_string($value) && self::$autoConvertFloatSeparator) {
                    $value = str_replace(',', '.', $value);
                }
                $value = floatval($value);
                break;
            case 'str' :
            case 'string' :
            case 'text' :
                $value = trim(filter_var($value, FILTER_SANITIZE_STRING));
                break;

            case 'cleaned_string' :
                $value = trim(filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
                break;

            case 'email' :
                $value = filter_var($value, FILTER_SANITIZE_EMAIL);
                break;

            case 'url' :
                $value = filter_var($value, FILTER_SANITIZE_URL);
                break;

            case 'raw' :
                break;
            case 'alphanum' :
                $value = preg_replace("/[^A-Za-z0-9_]/i", '', $value);
                break;
            case 'num'    :
                $value = preg_replace("/[^0-9]/i", '', $value);
                break;
            case 'login' :
                $value = preg_replace("/[^A-Za-z0-9_@\.\-]/i", '', $value);
                break;
            case 'pagecode' :
                $value = preg_replace("/[^a-z0-9_-]/i", '', strtolower((string) $value));
                $value = str_replace(' ', "-", $value);
                break;

            case 'alpha' :
                $value = preg_replace("/[^A-Za-z]/i", '', $value);
                break;

            default :
                $value = intval($value);
                break;

        }
        return $value;
    }
}