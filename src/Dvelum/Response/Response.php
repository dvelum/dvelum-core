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

namespace Dvelum\Response;

class Response implements ResponseInterface
{
    /**
     * @var string
     */
    protected string $format = self::FORMAT_HTML;
    /**
     * @var string
     */
    protected string $buffer = '';
    /**
     * @var bool
     */
    protected bool $sent = false;

    /**
     * Send redirect header
     * @param mixed $location
     */
    public function redirect($location): void
    {
        header("Location: $location");
        exit();
    }

    /**
     * Add string to response buffer
     * @param string $string
     */
    public function put(string $string): void
    {
        if ($this->sent) {
            trigger_error('The response was already sent');
        }
        $this->buffer .= $string;
    }

    /**
     * Send response, finish request
     */
    public function send(): void
    {
        if ($this->sent) {
            throw new \Exception('Response already sent');
        }

        if ($this->format === self::FORMAT_JSON) {
            $this->header('Content-Type: application/json');
        }

        echo $this->buffer;

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        $this->sent = true;
    }

    /**
     * Send error message
     * @param string $message
     * @param array $errors
     * @return void
     * @throws \Exception
     */
    public function error(string $message, array $errors = []): void
    {
        @ob_clean();
        switch ($this->format) {
            case self::FORMAT_JSON:
                $message = json_encode(['success' => false, 'msg' => $message, 'errors' => $errors]);
                break;
            case self::FORMAT_HTML:
                $this->notFound();
        }
        $this->put((string)$message);
        $this->send();
    }

    /**
     * Send success response
     * @param array $data
     * @param array $params
     * @return void
     */
    public function success(array $data = [], array $params = []): void
    {
        $message = '';
        switch ($this->format) {
            case self::FORMAT_HTML:
            case self::FORMAT_JSON:
                $message = ['success' => true, 'data' => $data];
                if (!empty($params)) {
                    $message = array_merge($message, $params);
                }
                $message = json_encode($message);
                break;
        }
        $this->put((string)$message);
        $this->send();
    }

    /**
     * Set response format
     * @param string $format
     * @return void
     */
    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    /**
     * Send JSON
     * @param array $data
     * @return void
     */
    public function json(array $data = []): void
    {
        $this->put((string)json_encode($data));
        $this->send();
    }

    /**
     * Send 404 Response header
     * @return void
     */
    public function notFound(): void
    {
        if (isset($_SERVER["SERVER_PROTOCOL"])) {
            $this->header($_SERVER["SERVER_PROTOCOL"] . "/1.0 404 Not Found");
        }
    }

    /**
     * Send response header
     * @param string $string
     * @return void
     */
    public function header(string $string): void
    {
        \header($string);
    }

    /**
     * Is sent
     * @return bool
     */
    public function isSent(): bool
    {
        return $this->sent;
    }

    /**
     * @param int $code
     */
    public function setResponseCode(int $code): void
    {
        if (function_exists('http_response_code')) {
            http_response_code($code);
        } else {
            header('X-PHP-Response-Code: ' . $code, true, $code);
        }
    }
}
