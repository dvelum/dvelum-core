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
/**
 * Class for building SQL SELECT queries
 * Represents an implementation of the Zend_Db_select interface,
 * features simplified logic and better performance.
 * Functionality is practically identical to that of Zend_Db_Select, so it is easy to use for those, who is familiar with the latter.
 * introduced in DVelum 0.9
 */
class Select
{
    const JOIN_INNER = 'inner';
    const JOIN_LEFT = 'left';
    const JOIN_RIGHT = 'right';
    /**
     * @var bool
     */
    public $localCache = true;

    /**
     * @var Adapter|false $dbAdapter
     */
    protected $dbAdapter = false;
    /**
     * @var bool
     */
    protected $distinct = false;
    /**
     * @var array
     */
    protected $from;
    /**
     * @var array
     */
    protected $where;
    /**
     * @var array
     */
    protected $join;
    /**
     * @var array
     */
    protected $group;
    /**
     * @var array
     */
    protected $having;
    /**
     * @var array
     */
    protected $limit;
    /**
     * @var array
     */
    protected $order;
    /**
     * @var array
     */
    protected $orWhere;
    /**
     * @var array
     */
    protected $orHaving;
    /**
     * @var bool
     */
    protected $forUpdate;
    /**
     * @var array
     */
    protected $assembleOrder = [
        '_getDistinct' => 'distinct',
        '_getFrom' => 'from',
        '_getJoins' => 'join',
        '_getWhere' => 'where',
        'getOrWhere' => 'orWhere',
        'getGroup' => 'group',
        'getHaving' => 'having',
        'getOrHaving' => 'orHaving',
        'getOrder' => 'order',
        'getLimit' => 'limit',
        'getForUpdate' => 'forUpdate'

    ];
    /**
     * @var array
     */
    protected $aliasCount = [];

    /**
     * @param Adapter $adapter
     * @return void
     */
    public function setDbAdapter(Adapter $adapter) : void
    {
        $this->dbAdapter = $adapter;
    }

    /**
     * Add a DISTINCT clause
     * @return self
     */
    public function distinct() : self
    {
        $this->distinct = true;
        return $this;
    }

    /**
     * Add a FROM clause to the query
     * @param mixed $table string table name or array('alias'=>'tablename')
     * @param mixed $columns
     * @return self
     */
    public function from($table, $columns = "*") : self
    {
        if (!is_array($columns)) {
            if ($columns !== '*')
                $columns = $this->convertColumnsString($columns);
            else
                $columns = [$columns];
        }

        $this->from = ['table' => $table, 'columns' => $columns];
        return $this;
    }

    /**
     * Set columns
     * @param array|string $columns
     * @return self
     */
    public function columns($columns = "*") : self
    {
        if (!is_array($columns)) {
            if ($columns !== '*')
                $columns = $this->convertColumnsString($columns);
            else
                $columns = [$columns];
        }
        $this->from['columns'] = $columns;
        return $this;
    }

    /**
     * Add a WHERE clause
     * @param string $condition
     * @param mixed $bind
     * @return self
     */
    public function where($condition, $bind = false)  : self
    {
        if (!is_array($this->where))
            $this->where = array();

        $this->where[] = array('condition' => $condition, 'bind' => $bind);
        return $this;
    }

    /**
     * Add a OR WHERE clause to the query
     * @param string $condition
     * @param mixed $bind
     * @return self
     */
    public function orWhere($condition, $bind = false) : self
    {
        if (!is_array($this->orWhere))
            $this->orWhere = array();

        $this->orWhere[] = array('condition' => $condition, 'bind' => $bind);

        return $this;
    }

    /**
     * Add a GROUP clause to the query
     * @param mixed $fields string field name or array of field names
     * @return self
     */
    public function group($fields) : self
    {
        if (!is_array($this->group))
            $this->group = array();

        if (!is_array($fields))
            $fields = explode(',', $fields);

        foreach ($fields as $field)
            $this->group[] = $field;

        return $this;
    }

    /**
     * Add a HAVING clause to the query
     * @param string $condition
     * @param mixed $bind
     * @return self
     */
    public function having($condition, $bind = false) : self
    {
        if (!is_array($this->having))
            $this->having = array();

        $this->having[] = array('condition' => $condition, 'bind' => $bind);

        return $this;
    }

    /**
     * Add a OR HAVING clause to the query
     * @param string $condition
     * @param mixed $bind
     * @return self
     */
    public function orHaving($condition, $bind = false) : self
    {
        if (!is_array($this->orHaving))
            $this->orHaving = array();

        $this->orHaving[] = array('condition' => $condition, 'bind' => $bind);

        return $this;
    }

    /**
     * Adding another table to the query using JOIN
     * @param string $table
     * @param mixed $cond
     * @param mixed $cols
     * @param string $type
     * @return self
     */
    public function join($table, $cond, $cols = '*', string $type ='inner') : self
    {
        $this->addJoin($table, $cond, $cols, $type);

        return $this;
    }

    /**
     * Adding another table to the query using INNER JOIN
     * @param mixed $table
     * @param mixed $cond
     * @param mixed $cols
     * @deprecated
     * @return self
     */
    public function joinInner($table, $cond, $cols = '*') : self
    {
        $this->addJoin( $table, $cond, $cols, self::JOIN_INNER);

        return $this;
    }

    /**
     * Adding another table to the query using LEFT JOIN
     * @param mixed $table
     * @param mixed $cond
     * @param mixed $cols
     * @deprecated
     * @return self
     */
    public function joinLeft($table, $cond, $cols = '*') : self
    {
        $this->addJoin($table, $cond, $cols, self::JOIN_LEFT);

        return $this;
    }

    /**
     * Adding another table to the query using RIGHT JOIN
     * @param mixed $table
     * @param mixed $cond
     * @param mixed $cols
     * @deprecated
     * @return self
     */
    public function joinRight($table, $cond, $cols = '*') : self
    {
        $this->addJoin($table, $cond, $cols, self::JOIN_RIGHT);

        return $this;
    }

    /**
     * @param string|array $table
     * @param string $cond
     * @param array|string $cols
     * @param string $type
     * @return Select
     */
    protected function addJoin($table, $cond, $cols, string $type) : self
    {
        if (!is_array($table) || is_int(key($table))) {
            if (is_array($table))
                $table = $table[key($table)];

            if (!isset($this->aliasCount[$table]))
                $this->aliasCount[$table] = 0;

            $tableAlias = $table;

            if ($this->aliasCount[$table])
                $tableAlias = $table . '_' . $this->aliasCount[$table];

            $this->aliasCount[$table]++;

            $table = array($tableAlias => $table);
        } else {
            $key = key($table);
            $table = array($key => $table[$key]);
        }

        if (!is_array($cols)) {
            if ($cols !== '*')
                $cols = $this->convertColumnsString($cols);
            else
                $cols = array($cols);
        }

        if (!is_array($this->join))
            $this->join = array();

        $this->join[] = array('type' => $type, 'table' => $table, 'condition' => $cond, 'columns' => $cols);

        return $this;
    }

    /**
     * Adding a LIMIT clause to the query
     * @param int $count
     * @param mixed $offset - optional
     * @return self
     */
    public function limit(int $count, $offset = false) : self
    {
        $this->limit = ['count' => $count, 'offset' => $offset];

        return $this;
    }

    /**
     * Adding offset
     * @param int $offset
     * @return self
     */
    public function offset($offset) : self
    {
        $this->limit['offset'] = $offset;
        return $this;
    }

    /**
     * Setting the limit and count by page number.
     * @param int $page Limit results to this page number.
     * @param int $rowCount Use this many rows per page.
     * @return self
     */
    public function limitPage(int $page, int $rowCount) : self
    {
        $page = ($page > 0) ? $page : 1;
        $rowCount = ($rowCount > 0) ? $rowCount : 1;
        $this->limit = array('count' => $rowCount, 'offset' => $rowCount * ($page - 1));
        return $this;
    }

    /**
     * Adding an ORDER clause to the query
     * @param mixed $spec
     * @param boolean $asIs optional
     * @return self
     */
    public function order($spec, $asIs = false) : self
    {
        if ($asIs) {
            $this->order = array($spec);
            return $this;
        }

        $result = array();
        if (!is_array($spec)) {
            $items = explode(',', $spec);
            foreach ($items as $str) {
                $str = trim($str);
                $wArray = explode(' ', $str);
                $wArray[0] = $this->quoteIdentifier($wArray[0]);
                $result[] = implode(' ', $wArray);
            }
        } else {
            foreach ($spec as $key => $type) {
                if (is_int($key)) {
                    if (strpos(trim($type), ' '))
                        $result[] = $type;
                    else
                        $result[] = $this->quoteIdentifier($type);
                } else {
                    $result[] = $this->quoteIdentifier($key) . ' ' . strtoupper($type);
                }
            }
        }
        $this->order = $result;
        return $this;
    }

    /**
     * Makes the query SELECT FOR UPDATE.
     *
     * @param bool $flag Whether or not the SELECT is FOR UPDATE (default true).
     * @return self
     */
    public function forUpdate($flag = true) : self
    {
        $this->forUpdate = $flag;
        return $this;
    }

    public function __toString() : string
    {
        return $this->assemble();
    }

    /**
     * @return string
     */
    public function assemble() : string
    {
        $sql = 'SELECT ';
        foreach ($this->assembleOrder as $method => $data)
            if (!empty($this->$data))
                $sql = $this->$method($sql);
        return $sql . ';';
    }

    /**
     * @param string $sql
     * @return string
     */
    protected function _getDistinct(string $sql) : string
    {
        if ($this->distinct)
            $sql .= 'DISTINCT ';

        return $sql;
    }
    /**
     * @param string $sql
     * @return string
     */
    protected function _getFrom($sql) : string
    {
        $columns = $this->tableFieldsList($this->from['table'], $this->from['columns']);
        $tables = array();

        $tables[] = $this->tableAlias($this->from['table']);

        if (!empty($this->join))
            foreach ($this->join as $config)
                $columns = array_merge($columns, $this->tableFieldsList($config['table'], $config['columns']));

        $sql .= implode(', ', $columns) . ' FROM ' . implode(', ', $tables);

        return $sql;
    }
    /**
     * @param string $sql
     * @return string
     */
    protected function _getJoins($sql)
    {
        foreach ($this->join as $item)
            $sql .= $this->compileJoin($item);

        return $sql;
    }

    protected function compileJoin(array $config) : string
    {
        $str = '';
        //type, table , condition
        switch ($config['type']) {
            case self::JOIN_INNER :
                $str .= ' INNER JOIN ';
                break;
            case self::JOIN_LEFT :
                $str .= ' LEFT JOIN ';
                break;
            case self::JOIN_RIGHT :
                $str .= ' RIGHT JOIN ';
                break;
        }

        $str .= $this->tableAlias($config['table']) . ' ON ' . $config['condition'];
        return $str;
    }
    /**
     * @param string $sql
     * @return string
     */
    protected function _getWhere($sql) : string
    {
        $where = $this->prepareWhere($this->where);

        return $sql . ' WHERE (' . implode(' AND ', $where) . ')';
    }

    /**
     * @param array $list
     * @return array
     */
    protected function prepareWhere(array $list) : array
    {
        $where = [];

        foreach ($list as $item) {
            if ($item['bind'] === false) {
                $where[] = $item['condition'];
            } else {
                if (is_array($item['bind'])) {
                    $list = [];
                    foreach ($item['bind'] as $listValue){
                        if(is_numeric($listValue)){
                            // disable quoting for numeric values
                            $list[] = $listValue;
                        }else{
                            $list[] = $this->quote($listValue);
                        }
                    }
                    $item['bind'] = implode(',', $list);
                } else {
                    $item['bind'] = $this->quote((string)$item['bind']);
                }
                $where[] = str_replace('?', $item['bind'], $item['condition']);
            }
        }
        return $where;
    }
    /**
     * @param string $sql
     * @return string
     */
    protected function getOrWhere(string $sql) : string
    {
        $where = $this->prepareWhere($this->orWhere);
        return $sql . ' OR (' . implode(' ) OR ( ', $where) . ')';
    }
    /**
     * @param string $sql
     * @return string
     */
    protected function getHaving(string $sql) : string
    {
        $having = $this->prepareWhere($this->having);
        return $sql . ' HAVING (' . implode(' AND ', $having) . ')';
    }
    /**
     * @param string $sql
     * @return string
     */
    protected function getOrHaving(string $sql) : string
    {
        $having = $this->prepareWhere($this->orHaving);
        return $sql . ' OR (' . implode(' ) OR ( ', $having) . ')';
    }
    /**
     * @param string $sql
     * @return string
     */
    protected function getGroup(string $sql) : string
    {
        foreach ($this->group as &$item)
            $item = $this->quoteIdentifier($item);

        return $sql . ' GROUP BY ' . implode(',', $this->group);
    }
    /**
     * @param string $sql
     * @return string
     */
    protected function getOrder(string $sql) : string
    {
        return $sql . ' ORDER BY ' . implode(',', $this->order);
    }
    /**
     * @param string $sql
     * @return string
     */
    protected function getLimit(string $sql) : string
    {
        if ($this->limit['offset'])
            return $sql . ' LIMIT ' . intval($this->limit['offset']) . ',' . $this->limit['count'];
        else
            return $sql . ' LIMIT ' . $this->limit['count'];
    }
    /**
     * @param string $sql
     * @return string
     */
    protected function getForUpdate(string $sql) : string
    {
        if ($this->forUpdate) {
            return $sql . ' FOR UPDATE';
        } else {
            return $sql;
        }
    }

    /**
     * Quote a string as an identifier
     * @param string $str
     * @return string
     */
    public function quoteIdentifier(string $str) : string
    {
        return '`' . str_replace(array('`', '.'), array('', '`.`'), $str) . '`';
    }

    /**
     * Quote a raw string.
     * @param mixed $value Raw string
     * @return string Quoted string
     */
    protected function quote($value) : string
    {
        if (is_int($value)) {
            return (string) $value;
        } elseif (is_float($value)) {
            return sprintf('%F', $value);
        }

        if($this->dbAdapter){
            return $this->dbAdapter->quote((string)$value);
        }else{
            return '\'' . addcslashes((string) $value, "\x00\n\r\\'\"\x1a") . '\'';
        }
    }

    /**
     * @param array|string $table
     * @return string
     */
    protected function tableAlias($table) : string
    {
        static $cache = [];

        $hash =  null;

        // performance patch
        if ($this->localCache) {
            if (is_array($table))
                $hash = md5(serialize($table));
            else
                $hash = $table;

            if (isset($cache[$hash]))
                return $cache[$hash];
        }

        if (!is_array($table)) {
            $data = $this->quoteIdentifier($table);
        } else {
            $key = key($table);

            if (is_int($key))
                $data = $this->quoteIdentifier($table[$key]);
            else
                $data = $this->quoteIdentifier($table[$key]) . ' AS ' . $this->quoteIdentifier((string)$key);
        }

        if ($this->localCache)
            $cache[$hash] = $data;

        return $data;
    }

    /**
     * @param mixed $table
     * @param array $columns
     * @return array
     */
    protected function tableFieldsList($table, array $columns) : array
    {
        static $cache = [];
        $hash = '';

        // performance patch
        if ($this->localCache) {
            $hash = md5(serialize(func_get_args()));
            if (isset($cache[$hash]))
                return $cache[$hash];
        }

        $result = [];

        if (is_array($table)) {
            $key = key($table);

            if (is_int($key))
                $table = $table[$key];
            else
                $table = $key;
        }

        foreach ($columns as $k => $v) {
            $v = (string) $v;
            $wordsCount = str_word_count($v, 0, "_*\"");

            if (is_int($k)) {

                if (!strlen($v))
                    continue;

                if ($v === '*') {
                    $result[] = $this->quoteIdentifier($table) . '.*';
                } else {
                    if ($wordsCount === 1)
                        $result[] = $this->quoteIdentifier($table . '.' . $v);
                    else
                        $result[] = $v;
                }
            } else {
                if (!strlen($v) || !strlen($k))
                    continue;

                if ($wordsCount === 1)
                    $v = $this->quoteIdentifier($table . '.' . $v);

                $result[] = $v . ' AS ' . $this->quoteIdentifier($k);
            }
        }

        if ($this->localCache)
            $cache[$hash] = $result;

        return $result;
    }

    /**
     * @param string $str
     * @return array
     */
    protected function convertColumnsString(string $str) : array
    {
        $items = explode(',', $str);
        return array_map('trim', $items);
    }
}