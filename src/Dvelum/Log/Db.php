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

namespace Dvelum\Log;

use Psr\Log\LogLevel;

/**
 * Database log
 * Class Db
 * @package Dvelum\Log
 */
class Db extends \Psr\Log\AbstractLogger implements LogInterface
{
    /**
     * Database Table
     * @var string
     */
    protected $table;
    /**
     * Database connection
     * @var \Dvelum\Db\Adapter
     */
    protected $db;
    /**
     * Log name
     * @var string
     */
    protected $name;
    /**
     * @var array
     */
    protected $logFields = [
        'name' => 'name',
        'message' => 'message',
        'date' => 'date',
        'level' => 'level',
        'context' => 'context'
    ];
    /**
     * @var string
     */
    protected $lastError = '';

    /**
     * Db constructor.
     * @param string $logName
     * @param \Dvelum\Db\Adapter $dbConnection
     * @param string $tableName
     */
    public function __construct(string $logName, \Dvelum\Db\Adapter $dbConnection, string $tableName)
    {
        $this->name = $logName;
        $this->table = $tableName;
        $this->db = $dbConnection;
    }

    /**
     * Set log message
     * @param MixedLog $level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function log($level, $message, array $context = []): bool
    {
        try {
            $this->db->insert(
                $this->table,
                [
                    $this->logFields['name'] => $this->name,
                    $this->logFields['message'] => htmlentities($message),
                    $this->logFields['date'] => date('Y-m-d H:i:s'),
                    $this->logFields['level'] => json_encode($context)
                ]
            );
            return true;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function logError(string $message, array $context = []): bool
    {
        return $this->log(LogLevel::ERROR, $message, $context);
    }


    /**
     * Set database adapter
     * @param \Dvelum\Db\Adapter $db
     * @return void
     */
    public function setDbConnection(\Dvelum\Db\Adapter $db): void
    {
        $this->db = $db;
    }

    /**
     * Get last error
     * @return string
     */
    public function getLastError(): string
    {
        return $this->lastError;
    }

    /**
     * Set DB table
     * @param string $table
     * @return void
     */
    public function setTable(string $table): void
    {
        $this->table = $table;
    }
}
