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

use Zend\Db\Metadata\Source\MysqlMetadata;
use Zend\Db\Adapter\Adapter;

class Mysql extends MysqlMetadata
{
    protected function loadColumnData($table, $schema)
    {
        if (isset($this->data['columns'][$schema][$table])) {
            return;
        }
        $this->prepareDataHierarchy('columns', $schema, $table);
        $p = $this->adapter->getPlatform();

        $isColumns = [
            ['C', 'ORDINAL_POSITION'],
            ['C', 'COLUMN_DEFAULT'],
            ['C', 'IS_NULLABLE'],
            ['C', 'DATA_TYPE'],
            ['C', 'CHARACTER_MAXIMUM_LENGTH'],
            ['C', 'CHARACTER_OCTET_LENGTH'],
            ['C', 'NUMERIC_PRECISION'],
            ['C', 'NUMERIC_SCALE'],
            ['C', 'COLUMN_NAME'],
            ['C', 'COLUMN_TYPE'],
            ['C', 'EXTRA']
        ];

        array_walk($isColumns, function (&$c) use ($p) {
            $c = $p->quoteIdentifierChain($c);
        });

        $sql = 'SELECT ' . implode(', ', $isColumns)
            . ' FROM ' . $p->quoteIdentifierChain(['INFORMATION_SCHEMA', 'TABLES']) . 'T'
            . ' INNER JOIN ' . $p->quoteIdentifierChain(['INFORMATION_SCHEMA', 'COLUMNS']) . 'C'
            . ' ON ' . $p->quoteIdentifierChain(['T', 'TABLE_SCHEMA'])
            . '  = ' . $p->quoteIdentifierChain(['C', 'TABLE_SCHEMA'])
            . ' AND ' . $p->quoteIdentifierChain(['T', 'TABLE_NAME'])
            . '  = ' . $p->quoteIdentifierChain(['C', 'TABLE_NAME'])
            . ' WHERE ' . $p->quoteIdentifierChain(['T', 'TABLE_TYPE'])
            . ' IN (\'BASE TABLE\', \'VIEW\')'
            . ' AND ' . $p->quoteIdentifierChain(['T', 'TABLE_NAME'])
            . '  = ' . $p->quoteTrustedValue($table);

        if ($schema != self::DEFAULT_SCHEMA) {
            $sql .= ' AND ' . $p->quoteIdentifierChain(['T', 'TABLE_SCHEMA'])
                . ' = ' . $p->quoteTrustedValue($schema);
        } else {
            $sql .= ' AND ' . $p->quoteIdentifierChain(['T', 'TABLE_SCHEMA'])
                . ' != \'INFORMATION_SCHEMA\'';
        }

        $results = $this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
        $columns = [];
        foreach ($results->toArray() as $row) {
            $erratas = [];
            $matches = [];
            if (preg_match('/^(?:enum|set)\((.+)\)$/i', $row['COLUMN_TYPE'], $matches)) {
                $permittedValues = $matches[1];
                if (preg_match_all(
                    "/\\s*'((?:[^']++|'')*+)'\\s*(?:,|\$)/",
                    $permittedValues,
                    $matches,
                    PREG_PATTERN_ORDER
                )
                ) {
                    $permittedValues = str_replace("''", "'", $matches[1]);
                } else {
                    $permittedValues = [$permittedValues];
                }
                $erratas['permitted_values'] = $permittedValues;
            }

            if(strpos($row['EXTRA'],'auto_increment')!==false){
                $isAutoIncrement = true;
            }else{
                $isAutoIncrement = false;
            }

            $columns[$row['COLUMN_NAME']] = [
                'ordinal_position'          => $row['ORDINAL_POSITION'],
                'column_default'            => $row['COLUMN_DEFAULT'],
                'is_nullable'               => ('YES' == $row['IS_NULLABLE']),
                'data_type'                 => $row['DATA_TYPE'],
                'character_maximum_length'  => $row['CHARACTER_MAXIMUM_LENGTH'],
                'character_octet_length'    => $row['CHARACTER_OCTET_LENGTH'],
                'numeric_precision'         => $row['NUMERIC_PRECISION'],
                'numeric_scale'             => $row['NUMERIC_SCALE'],
                'numeric_unsigned'          => (false !== strpos($row['COLUMN_TYPE'], 'unsigned')),
                'erratas'                   => $erratas,
                'auto_increment'            => $isAutoIncrement
            ];
        }
        $this->data['columns'][$schema][$table] = $columns;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumn($columnName, $table, $schema = null)
    {
        if ($schema === null) {
            $schema = $this->defaultSchema;
        }

        $this->loadColumnData($table, $schema);

        if (! isset($this->data['columns'][$schema][$table][$columnName])) {
            throw new \Exception('A column by that name was not found.');
        }

        $info = $this->data['columns'][$schema][$table][$columnName];

        $column = new ColumnObject($columnName, $table, $schema);
        $props = [
            'ordinal_position', 'column_default', 'is_nullable',
            'data_type', 'character_maximum_length', 'character_octet_length',
            'numeric_precision', 'numeric_scale', 'numeric_unsigned',
            'erratas'
        ];
        foreach ($props as $prop) {
            if (isset($info[$prop])) {
                $column->{'set' . str_replace('_', '', $prop)}($info[$prop]);
            }
        }

        $column->setOrdinalPosition($info['ordinal_position']);
        $column->setColumnDefault($info['column_default']);
        $column->setIsNullable($info['is_nullable']);
        $column->setDataType($info['data_type']);
        $column->setCharacterMaximumLength($info['character_maximum_length']);
        $column->setCharacterOctetLength($info['character_octet_length']);
        $column->setNumericPrecision($info['numeric_precision']);
        $column->setNumericScale($info['numeric_scale']);
        $column->setNumericUnsigned($info['numeric_unsigned']);
        $column->setErratas($info['erratas']);
        $column->setAutoIncrement($info['auto_increment']);

        return $column;
    }
}
