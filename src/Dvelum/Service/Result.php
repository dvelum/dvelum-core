<?php
/**
 * DVelum project https://github.com/dvelum/dvelum-core , https://github.com/dvelum/dvelum
 *
 * MIT License
 *
 * Copyright (C) 2011-2021  Kirill Yegorov
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

namespace App\Service;

class Result implements ResultInterface
{
    private int $statusCode;

    private ?string $error = null;
    /**
     * @var array<string,mixed>
     */
    private array $debugStat = [];
    /**
     * @var array<int|string,mixed>
     */
    private array $data = [];
    /**
     * HTTP Код ошибки
     * @var int|null
     */
    private ?int $httpErrorCode = null;

    public function getStatus(): int
    {
        return $this->statusCode;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getDebugStat(): array
    {
        return $this->debugStat;
    }

    public function setError(string $message): void
    {
        $this->error = $message;
    }

    public function setStatus(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function setDebugStat(array $data): void
    {
        $this->debugStat = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<mixed,mixed> $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return int|null
     */
    public function getHttpErrorCode(): ?int
    {
        return $this->httpErrorCode;
    }

    /**
     * @param int $code
     */
    public function setHttpErrorCode(int $code): void
    {
        $this->httpErrorCode = $code;
    }

    /**
     *
     */
    public function resetHttpErrorCode(): void
    {
        $this->httpErrorCode = null;
    }
}