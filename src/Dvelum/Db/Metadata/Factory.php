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

namespace Dvelum\Db\Metadata;


use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Exception\InvalidArgumentException;
use Zend\Db\Metadata\MetadataInterface;
use Zend\Db\Metadata\Source;

/**
 * Source metadata factory.
 */
class Factory
{
    /**
     * Create source from adapter
     *
     * @param  AdapterInterface $adapter
     * @return MetadataInterface
     * @throws InvalidArgumentException If adapter platform name not recognized.
     */
    public static function createSourceFromAdapter(AdapterInterface $adapter)
    {
        /**
         * @var  \Zend\Db\Adapter\Adapter $adapter
         */

        $platformName = $adapter->getPlatform()->getName();

        switch ($platformName) {
            case 'MySQL':
                return new Mysql($adapter);
            case 'SQLServer':
                return new Source\SqlServerMetadata($adapter);
            case 'SQLite':
                return new Source\SqliteMetadata($adapter);
            case 'PostgreSQL':
                return new Source\PostgresqlMetadata($adapter);
            case 'Oracle':
                return new Source\OracleMetadata($adapter);
            default:
                throw new InvalidArgumentException("Unknown adapter platform '{$platformName}'");
        }
    }
}