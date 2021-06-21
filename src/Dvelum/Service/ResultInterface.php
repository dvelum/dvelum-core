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

use App\Partkom\Client;
use App\Partkom\Part\Info as PartInfo;
use App\Partkom\Log\Pricing as PricingLog;

/**
 * Interface ResultInterface
 * Интерфейс системной информации о результатах запуска сервиса
 * @package App\Service
 */
interface ResultInterface
{
    public const RESULT_SUCCESS = 0;
    public const RESULT_ERROR = 1;
    public const RESULT_NO_DATA = 2;

    /**
     * Получить статус результата (константа RESULT_*)
     * @return int
     */
    public function getStatus():int;

    /**
     * Получить текст ошибки
     * @return string|null
     */
    public function getError():?string;

    /**
     * Задать сообщение об ошибке
     * @param string $message
     */
    public function setError(string $message):void;

    /**
     * @return array<string,mixed>
     */
    public function getDebugStat():array;

    /**
     * Данные которые возвращает сервис
     * @return array<int|string,mixed>
     */
    public function getData(): array;

    /**
     * Установить данные результата обработки запроса
     * @param array<mixed,mixed> $data
     */
    public function setData(array $data): void;
    /**
     * @return int|null
     */
    public function getHttpErrorCode(): ?int;

    /**
     * Задать код ответа при ошибке
     * @param int $code
     */
    public function setHttpErrorCode(int $code): void;
}