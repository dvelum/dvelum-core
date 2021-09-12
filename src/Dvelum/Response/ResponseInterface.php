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

namespace Dvelum\Response;

interface ResponseInterface
{
    public const FORMAT_HTML = 'html';
    public const FORMAT_JSON = 'json';

    /**
     * Send redirect header
     * @param string $location
     */
    public function redirect(string $location): void;

    /**
     * Add string to response buffer
     * @param string $string
     */
    public function put(string $string): void;

    /**
     * Send response, finish request
     */
    public function send(): void;

    /**
     * Send error message
     * @param string $message
     * @param array<mixed> $errors
     * @return void
     * @throws \Exception
     */
    public function error(string $message, array $errors = []): void;

    /**
     * Send success response
     * @param array<int|string,mixed> $data
     * @param array<int|string,mixed> $params
     * @return void
     */
    public function success(array $data = [], array $params = []): void;

    /**
     * Set response format
     * @param string $format
     * @return void
     */
    public function setFormat(string $format): void;

    /**
     * Send JSON
     * @param array<int|string,mixed> $data
     * @return void
     */
    public function json(array $data = []): void;

    /**
     * Send 404 Response header
     * @return void
     */
    public function notFound(): void;

    /**
     * Send response header
     * @param string $string
     * @return void
     */
    public function header(string $string): void;

    /**
     * Is sent
     * @return bool
     */
    public function isSent(): bool;

    /**
     * @param int $code
     */
    public function setResponseCode(int $code): void;
}
