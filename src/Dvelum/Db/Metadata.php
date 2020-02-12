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

namespace Dvelum\Db;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db;
use Zend\Db\Metadata\MetadataInterface;
use Zend\Db\Metadata\Object\ColumnObject;
use Zend\Db\Metadata\Object\ConstraintObject;
use Dvelum\Db\Metadata\Factory;

class Metadata
{
    /**
     * @var MetadataInterface $metadata
     */
    protected $metadata;

    public function __construct(AdapterInterface $db)
    {
        $this->metadata = Factory::createSourceFromAdapter($db);
    }

    public function findPrimaryKey(string $tableName) : ?string
    {
        foreach ($this->metadata->getConstraints($tableName) as $constraint) {
            /**
             * @var Db\Metadata\Object\ConstraintObject $constraint
             */
            if (!$constraint->hasColumns()) {
                continue;
            }
            if ($constraint->isPrimaryKey()) {
                return $constraint->getColumns()[0];
            }
        }
        return null;
    }

    public function getAdapter() : MetadataInterface
    {
        return $this->metadata;
    }

    /**
     * @return string[]
     */
    public function getTableNames(): array
    {
        return $this->metadata->getTableNames();
    }

    /**
     * @param string $tableName
     * @return array
     */
    public function getColumns(string $tableName) : array
    {
        $data = [];
        foreach ($this->metadata->getColumns($tableName) as $column){
            /**
             * @var Db\Metadata\Object\ColumnObject $column
             */
            $name = $column->getName();
            $data[$name] = $column;
        }
        return $data;
    }

    /**
     * @param string $tableName
     * @return ConstraintObject[]
     */
    public function getConstraints(string $tableName) : array
    {
        return $this->metadata->getConstraints($tableName);
    }

    public function getColumnsAsArray(string $tableName) : array
    {
        $data = [];
        foreach ($this->metadata->getColumns($tableName) as $column){
            /**
             * @var Db\Metadata\Object\ColumnObject $column
             */
            $name = $column->getName();
            $data[$name] = [
              'name' => $name,
              'data_type'=> $column->getDataType(),
              'erratas' =>$column->getErratas(),
              'max_len'  => $column->getCharacterMaximumLength(),
              'octet_length'  => $column->getCharacterOctetLength(),
              'default' => $column->getColumnDefault(),
              'null' => $column->getIsNullable(),
              'unsigned' => $column->getNumericUnsigned(),
              'scale'=> $column->getNumericScale(),
              'precision'=> $column->getNumericPrecision()
            ];
        }
        return $data;
    }

    public function indexHashByColumns(array $columns) : string
    {
        return implode('_', $columns);
    }
}