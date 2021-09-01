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

use Dvelum\Config\ConfigInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Request wrapper
 * @author Kirill Yegorov 2008
 * @package Dvelum
 */
class Request
{
    /**
     * @var array<string,mixed> $config
     */
    protected array $config = [];

    /**
     * @var string $uri
     */
    protected string $uri;

    /**
     * Uri parts
     * @var array<int,string>
     */
    protected array $parts = [];
    /**
     * @var array<string,mixed>
     */
    protected array $updatedGet = [];
    /**
     * @var array<string,mixed>
     */
    protected array $updatedPost = [];

    public function __construct()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (empty($uri)) {
            $uri = '/';
        }
        $this->uri = $this->parseUri($uri);
        $this->parts = $this->detectParts($this->uri);
    }

    /**
     * @param string $string
     * @return string
     */
    protected function parseUri(string $string): string
    {
        if (strpos($string, '?') !== false) {
            $string = substr($string, 0, strpos($string, '?'));
        }

        $string = str_ireplace(
            [
                '.html',
                '.php',
                '.xml',
                '.phtml',
                '.json'
            ],
            '',
            $string
        );

        return (string)preg_replace("/[^A-Za-z0-9_\.\-\/]/i", '', $string);
    }


    /**
     * Explode request URI to parts
     * @param string $uri
     * @return array<int,string>
     */
    protected function detectParts(string $uri): array
    {
        $parts = [];

        $wwwRoot = $this->wwwRoot();
        $rootLen = strlen($wwwRoot);

        if (substr($uri, 0, $rootLen) === $wwwRoot) {
            $uri = substr($uri, $rootLen);
        }

        $array = explode('/', $uri);

        for ($i = 0, $sz = count($array); $i < $sz; $i++) {
            $parts[] = $array[$i];
        }

        return $parts;
    }


    /**
     * Set configuration options
     * @param array<string,mixed> $config
     * @return void
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * Set configuration option value
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setConfigOption(string $name, $value): void
    {
        $this->config['name'] = $value;
    }

    /**
     * Get request part by index
     * The query string is divided into parts by the delimiter defined by the
     * method Request::setDelimiter are indexed with  0
     * @param int $index — index of the part
     * @return null|string
     */
    public function getPart(int $index): ?string
    {
        if (isset($this->parts[$index]) && strlen((string)$this->parts[$index])) {
            return $this->parts[$index];
        }

        return null;
    }

    /**
     * Get parameter transferred by the method $_GET
     * @param string $name — parameter name
     * @param string $type — the value type defining the way the data will be filtered.
     * The ‘Filter’ chapter expands on the list of supported types. Here is the basic list:
     * integer , boolean , float , string, cleaned_string , array и др.
     * @param mixed $default — default value if the parameter is missing.
     * @return mixed
     */
    public function get(string $name, string $type, $default)
    {
        if (isset($this->updatedGet[$name])) {
            return Filter::filterValue($type, $this->updatedGet[$name]);
        }

        if (!isset($_GET[$name])) {
            return $default;
        }

        return Filter::filterValue($type, $_GET[$name]);
    }

    /**
     * Get all parameters passed by the $_POST method in an array
     * @return array<string,mixed>
     */
    public function postArray(): array
    {
        return array_merge($_POST, $this->updatedPost);
    }

    /**
     * Get all parameters passed by the $_GET method in an array
     * @return array<string,mixed>
     */
    public function getArray(): array
    {
        return array_merge($_GET, $this->updatedGet);
    }

    /**
     * Get the parameter passed by $_POST method
     * @param string $name — parameter name
     * @param string $type —   the value type defining the way the data will be filtered.
     * The ‘Filter’ chapter expands on the list of supported types. Here is the basic list:
     * integer , boolean , float , string, cleaned_string , array и др.
     * @param mixed $default — default value if  the parameter is missing.
     * @return mixed
     */
    public function post(string $name, string $type, $default)
    {
        if (isset($this->updatedPost[$name])) {
            return Filter::filterValue($type, $this->updatedPost[$name]);
        }

        if (!isset($_POST[$name])) {
            return $default;
        }

        return Filter::filterValue($type, $_POST[$name]);
    }

    /**
     * Validate the parameter passed by $_POST method
     * @param string $name — parameter name
     * @param string $type —   the value type defining the way the data will be filtered.
     * The ‘Filter’ chapter expands on the list of supported types. Here is the basic list:
     * integer , bool , float , string, cleaned_string , array и др.
     * @return bool
     */
    public function validatePost(string $name, string $type): bool
    {
        if (isset($this->updatedPost[$name])) {
            return ($this->updatedPost[$name] === Filter::filterValue($type, $this->updatedPost[$name]));
        }

        if (!isset($_POST[$name])) {
            return false;
        }

        return ($_POST[$name] === Filter::filterValue($type, $_POST[$name]));
    }

    /**
     * Build system request URL
     * The method creates a string based on the defined parameter delimiter and
     * the parameter values array
     * @param array<int,string> $parts — request parameters array
     * @return string
     */
    public function url(array $parts): string
    {
        return strtolower($this->wwwRoot() . implode('/', $parts));
    }

    /**
     * Process ExtJs Filters
     * @param string $container
     * @param string $method
     * @return array<int|string,mixed>
     */
    public function extFilters(string $container = 'storefilter', string $method = 'POST'): array
    {
        if ($method === 'POST') {
            $data = $this->post($container, 'raw', []);
        } else {
            $data = $this->get($container, 'raw', []);
        }

        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        if (empty($data)) {
            return [];
        }

        $filter = new Filter\ExtJs();

        return $filter->toDbSelect($data);
    }

    /**
     * Check if request is sent by XMLHttpRequest
     * @return bool
     */
    public function isAjax(): bool
    {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if any POST requests have been sent
     * @return bool
     */
    public function hasPost(): bool
    {
        if (empty($_POST) && empty($this->updatedPost)) {
            return false;
        }
        return true;
    }


    /**
     * Get cleaned request URL
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     *
     * Get the list of sent files
     * @return array<string,array<string,array{name:string,type:string,tmp_name:string,error:string,size:int}>>
     */
    public function files(): array
    {
        if (empty($_FILES)) {
            return [];
        }

        $result = [];

        foreach ($_FILES as $key => $data) {
            if (!isset($data['name'])) {
                continue;
            }

            if (!is_array($data['name'])) {
                $result[$key] = $data;
            } else {
                foreach ($data['name'] as $subKey => $value) {
                    $result[$key][$subKey] = [
                        'name' => $data['name'][$subKey],
                        'type' => $data['type'][$subKey],
                        'tmp_name' => $data['tmp_name'][$subKey],
                        'error' => $data['error'][$subKey],
                        'size' => $data['size'][$subKey]
                    ];
                }
            }
        }
        return $result;
    }

    /**
     * Check HTTP_SCHEME for https
     * @return bool
     */
    public function isHttps(): bool
    {
        static $scheme = false;

        if ($scheme === false) {
            if (isset($_SERVER['HTTP_SCHEME'])) {
                $scheme = $_SERVER['HTTP_SCHEME'];
            } else {
                if ($_SERVER['SERVER_PORT'] === 443 || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) {
                    $scheme = 'https';
                } else {
                    $scheme = 'http';
                }
            }
        }

        if ($scheme === 'https') {
            return true;
        }
        return false;
    }

    /**
     * Get web toot path
     * @return string
     */
    public function wwwRoot(): string
    {
        if (isset($this->config['wwwRoot'])) {
            return $this->config['wwwRoot'];
        }
        return '/';
    }

    /**
     * Get application base url
     * @return string
     */
    public function baseUrl()
    {
        $protocol = 'http://';
        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            $protocol = 'https://';
        }

        return $protocol . $_SERVER['HTTP_HOST'] . $this->wwwRoot();
    }

    /**
     * Get parameter transferred by the method $_SERVER
     * @param string $name — parameter name
     * @param string $type — the value type defining the way the data will be filtered.
     * The ‘Filter’ chapter expands on the list of supported types. Here is the basic list:
     * integer , boolean , float , string, cleaned_string , array и др.
     * @param mixed $default — default value if the parameter is missing.
     * @return mixed
     */
    public function server($name, $type, $default)
    {
        if (!isset($_SERVER[$name])) {
            return $default;
        }

        return Filter::filterValue($type, $_SERVER[$name]);
    }

    /**
     * Redefine $_POST parameter
     * @param string $name — parameter name
     * @param mixed $value — parameter value
     */
    public function updatePost(string $name, $value): void
    {
        $this->updatedPost[$name] = $value;
    }

    /**
     * Set POST data
     * @param array<string,mixed> $data
     */
    public function setPostParams(array $data): void
    {
        $this->updatedPost = $data;
    }

    /**
     * Set GET data
     * @param array<string,mixed> $data
     */
    public function setGetParams(array $data): void
    {
        $this->updatedGet = $data;
    }

    /**
     * Redefine $_GET parameter
     * @param string $name — parameter name
     * @param mixed $value — parameter value
     */
    public function updateGet(string $name, $value): void
    {
        $this->updatedGet[$name] = $value;
    }

    /**
     * Get request parts
     * The query string is divided into parts by the delimiter "/" and indexed from 0
     * @param int $offset , optional default 0 - index to start from
     * @return array<int,string>
     */
    public function getPathParts(int $offset = 0): array
    {
        return array_slice($this->parts, $offset);
    }

    /**
     * Set request URI
     * @param string $uri
     * @return void
     */
    public function setUri(string $uri): void
    {
        $this->uri = $this->parseUri($uri);
        $this->parts = $this->detectParts($this->uri);
    }

    /**
     * Get request cookie
     * @return array<string,mixed>
     */
    public function cookie(): array
    {
        if (!empty($_COOKIE)) {
            return $_COOKIE;
        }
        return [];
    }
}
