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

namespace Dvelum\Security;

use Dvelum\Request;
use Dvelum\Store\AdapterInterface;
use Dvelum\Store\Factory;
use Dvelum\Store\Session;
use Dvelum\Utils;
use Exception;

/**
 * Security_Csrf class handles creation and validation
 * of tokens aimed at anti-CSRF protection.
 * @author Kirill Egorov
 * @package Security
 * @uses Utils, AdapterInterface , Session , Request
 */
class Csrf
{
    /**
     * A constant value, the name of the header parameter carrying the token
     * @var string
     */
    public const HEADER_VAR = 'HTTP_X_CSRF_TOKEN';

    /**
     * A constant value, the name of the token parameter being passed by POST request
     * @var string
     */
    public const POST_VAR = 'xscrftoken';

    /**
     * Token lifetime (1 hour 3600s)
     * @var integer
     */
    protected static $lifetime = 3600;
    /**
     * Limit of tokens count to perform cleanup
     * @var integer
     */
    protected static $cleanupLimit = 300;

    /**
     * Token storage
     * @var AdapterInterface
     */
    protected static $storage;

    /**
     * Set token storage implementing the Store_interface
     * @param AdapterInterface $store
     * @return void
     */
    public static function setStorage(AdapterInterface $store): void
    {
        static::$storage = $store;
    }

    /**
     * Set config options (storage , lifetime , cleanupLimit)
     * @param array<string,mixed> $options
     * @return void
     * @throws Exception
     */
    public static function setOptions(array $options): void
    {
        if (isset($options['storage'])) {
            if ($options['storage'] instanceof AdapterInterface) {
                static::$storage = $options['storage'];
            } else {
                throw new Exception('invalid storage');
            }
        }

        if (isset($options['lifetime'])) {
            static::$lifetime = (int)($options['lifetime']);
        }

        if (isset($options['cleanupLimit'])) {
            static::$cleanupLimit = (int)($options['cleanupLimit']);
        }
    }

    /**
     * Csrf constructor.
     * @throws Exception
     */
    public function __construct()
    {
        if (!isset(self::$storage)) {
            self::$storage = Factory::get(Factory::SESSION, 'security_csrf');
        }
    }

    /**
     * Create and store token
     * @return string
     */
    public function createToken(): string
    {
        /*
         * Cleanup storage
         */
        if (self::$storage->getCount() > self::$cleanupLimit) {
            $this->cleanup();
        }

        $token = md5(Utils::getRandomString(16) . uniqid('', true));
        self::$storage->set($token, time());
        return $token;
    }

    /**
     * Check if token is valid
     * @param string $token
     * @return bool
     */
    public function isValidToken(string $token): bool
    {
        if (!self::$storage->keyExists($token)) {
            return false;
        }

        if (time() < (int)(self::$storage->get($token)) + self::$lifetime) {
            return true;
        } else {
            self::$storage->remove($token);
            return false;
        }
    }

    /**
     * Remove tokens with expired lifetime
     */
    public function cleanup(): void
    {
        $tokens = self::$storage->getData();
        $time = time();

        foreach ($tokens as $k => $v) {
            if (intval($v) + self::$lifetime < $time) {
                self::$storage->remove($k);
            }
        }
    }

    /**
     * Invalidate (remove) token
     * @param string $token
     * @return void
     */
    public function removeToken(string $token): void
    {
        self::$storage->remove($token);
    }

    /**
     * Check POST request for a token
     * @param string $tokenVar - Variable name in the request
     * @return bool
     */
    public function checkPost(Request $request, $tokenVar = self::POST_VAR): bool
    {
        $var = $request->post($tokenVar, 'string', false);
        if ($var !== false && $this->isValidToken($var)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check HEADER for a token
     * @param string $tokenVar - Variable name in the header
     * @return bool
     */
    public function checkHeader(Request $request, $tokenVar = self::HEADER_VAR): bool
    {
        $var = $request->server($tokenVar, 'string', false);
        if ($var !== false && $this->isValidToken($var)) {
            return true;
        } else {
            return false;
        }
    }
}
