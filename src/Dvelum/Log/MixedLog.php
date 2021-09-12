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

namespace Dvelum\Log;

use Psr\Log\LogLevel;

class MixedLog extends \Psr\Log\AbstractLogger implements LogInterface
{
    /**
     * @var File
     */
    protected File $logFile;
    /**
     * @var Db
     */
    protected Db $logDb;

    public function __construct(File $logFile, Db $logDb)
    {
        $this->logFile = $logFile;
        $this->logDb = $logDb;
    }

    /**
     * @param int|string $level
     * @param string $message
     * @param array<mixed,mixed> $context
     * @return bool
     */
    public function log($level, $message, array $context = []): bool
    {
        if (!$this->logDb->log($level, $message, $context)) {
            $this->logFile->log($level, $message, $context);
            $this->logFile->log(\Psr\Log\LogLevel::ERROR, $this->logDb->getLastError());
            return false;
        }
        return true;
    }

    /**
     * @param string $message
     * @param array<mixed,mixed> $context
     * @return bool
     */
    public function logError($message, array $context = []): bool
    {
        return $this->log(LogLevel::ERROR, $message, $context);
    }
}
