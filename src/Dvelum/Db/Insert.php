<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2017  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Dvelum\Db;

class Insert
{
    /**
     * @var Adapter $db
     */
    protected $db;

    public function __construct(Adapter $db)
    {
        $this->db = $db;
    }

    /**
     * Insert multiple rows (not safe but fast)
     * @param string $table
     * @param array $records
     * @param int $chunkSize, optional default 500
     * @param bool $ignore - optional default false Ignore errors
     * @throws \Exception
     * @return void
     */
    public function bulkInsert(string $table, array $records, int $chunkSize = 500, bool $ignore = false) : void
    {
        if (empty($records)) {
            return;
        }

        $chunks = array_chunk($records, $chunkSize);

        $keys = array_keys($records[key($records)]);

        foreach ($keys as &$key) {
            $key = $this->db->quoteIdentifier((string)$key);
        }
        unset($key);

        $keys = implode(',', $keys);

        foreach ($chunks as $rowset) {
            foreach ($rowset as &$row) {
                foreach ($row as &$colValue) {
                    if (is_bool($colValue)) {
                        $colValue = intval($colValue);
                    } elseif (is_null($colValue)) {
                        $colValue = 'NULL';
                    } else {
                        $colValue = $this->db->quote($colValue);
                    }
                }
                unset($colValue);
                $row = implode(',', $row);
            }
            unset($row);

            $sql = 'INSERT ';

            if ($ignore) {
                $sql .= 'IGNORE ';
            }

            $sql .= 'INTO ' . $this->db->quoteIdentifier($table) . ' (' . $keys . ') ' . "\n" . ' VALUES ' . "\n" . '(' . implode(')' . "\n" . ',(',
                    array_values($rowset)) . ') ' . "\n" . '';

            $this->db->query($sql);

        }
    }

    /**
     * Insert single record on duplicate key update
     * @param string $table
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public function onDuplicateKeyUpdate(string $table, array $data): void
    {
        if (empty($data)) {
            return;
        }

        $keys = array_keys($data);

        foreach ($keys as &$val) {
            $val = $this->db->quoteIdentifier((string)$val);
        }
        unset($val);

        $values = array_values($data);
        foreach ($values as &$val) {
            if(is_bool($val)){
                $val = intval($val);
            }elseif (is_null($val)){
                $val = 'NULL';
            }else{
                $val = $this->db->quote($val);
            }
        }
        unset($val);

        $sql = 'INSERT INTO ' . $this->db->quoteIdentifier($table) . ' (' . implode(',',
                $keys) . ') VALUES (' . implode(',', $values) . ') ON DUPLICATE KEY UPDATE ';

        $updates = [];
        foreach ($keys as $key) {
            $updates[] = $key . ' = VALUES(' . $key . ') ';
        }

        $sql .= implode(', ', $updates) . ';';
        $this->db->query($sql);
    }

    /**
     * Insert record
     * @param string $table
     * @param array $data
     * @return void
     */
    public function insert(string $table, array $data) : void
    {
        $this->db->insert($table, $data);
    }
}