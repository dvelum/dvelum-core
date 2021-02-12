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

namespace Dvelum\Db;

use Laminas\Db;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;


/**
 * Class Adapter
 * Db adapter proxy
 */
class Adapter
{
    public const EVENT_INIT = 0;
    public const EVENT_CONNECTION_ERROR =1;

    /**
     * @var array
     */
    protected $params;
    /**
     * @var \Laminas\Db\Adapter\Adapter $adapter
     */
    protected $adapter;
    /**
     * @var array
     */
    protected $listeners;
    /**
     * @var bool
     */
    private $inited = false;

    /**
     * Adapter constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * @return void
     */
    public function init() : void
    {
        if($this->inited){
            return;
        }

        $this->adapter = new \Laminas\Db\Adapter\Adapter($this->params);
        $this->inited = true;
        try{
            $this->adapter->getDriver()->getConnection()->connect();
        }catch (\Exception $e){
            $this->fireEvent(self::EVENT_CONNECTION_ERROR, ['message'=>$e->getMessage()]);
            return;
        }
        $this->fireEvent(self::EVENT_INIT);
    }

    /**
     * @return Db\Adapter\Adapter
     */
    public function getAdapter() :  Db\Adapter\Adapter
    {
        if(!$this->inited){
            $this->init();
        }
        return $this->adapter;
    }

    /**
     * Get Select query builder
     * @return Select
     */
    public function select() : Select
    {
        $select = new Select();
        $select->setDbAdapter($this);
        return $select;
    }

    /**
     * @return Sql
     */
    public function sql() : Sql
    {
        if(!$this->inited){
            $this->init();
        }
        return  new Sql($this->adapter);
    }

    /**
     * Get Query profiler
     * @return Db\Adapter\Profiler\ProfilerInterface|null
     */
    public function getProfiler(): ?Db\Adapter\Profiler\ProfilerInterface
    {
        if(!$this->inited){
            return null;
        }
        return $this->adapter->getProfiler();
    }

    /**
     * Fetch results
     * @param string|\Dvelum\Db\Select $sql
     * @return array
     */
    public function fetchAll($sql) : array
    {
        if(!$this->inited){
            $this->init();
        }

        $statement = $this->adapter->createStatement();
        $statement->setSQl($sql);
        $statement->prepare();

        $result = $statement->execute();
        if ($result instanceof ResultInterface && $result->isQueryResult())
        {
            /*
             *  Mysqli performance patch
             */
            if($this->params['adapter'] === 'Mysqli') {
                /**
                 * @var \mysqli_stmt $resource
                 */
                $resource = $result->getResource();
                $result = $resource->get_result();
                if($result){
                    /**
                     * @var \mysqli_result $result
                     */
                    $data = $result->fetch_all(MYSQLI_ASSOC);
                    if(!empty($data)){
                        return $data;
                    }
                }
                return [];
            }else{
                $resultSet = new ResultSet(ResultSet::TYPE_ARRAY);
                return $resultSet->initialize($result)->toArray();
            }
        }
        return [];
    }

    /**
     * Fetch column from result set
     * @param mixed $sql
     * @return array
     */
    public function fetchCol($sql) : array
    {
        if(!$this->inited){
            $this->init();
        }

        $statement = $this->adapter->createStatement();
        $statement->setSQl($sql);
        $statement->prepare();
        $result = $statement->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            /*
             *  Mysqli performance patch
             */
            if($this->params['adapter'] === 'Mysqli') {
                /**
                 * @var \mysqli_stmt $resource
                 */
                $resource = $result->getResource();
                $result = $resource->get_result();

                if($result){
                    /**
                     * @var \mysqli_result $result
                     */
                    $resultData = $result->fetch_all(MYSQLI_NUM);
                    if(!empty($resultData)){
                        $data = [];
                        foreach ($resultData as $item){
                            $data[] = $item[0];
                        }
                        return $data;
                    }
                }
                return [];
            }else {
                $resultSet = new ResultSet(ResultSet::TYPE_ARRAY);
                $resultSet->initialize($result);
                $result = [];
                if(!empty($resultSet)){
                    foreach ($resultSet as $item) {
                        if(!empty($item)){
                            foreach ($item as $v) {
                                $result[] = $v;
                                break;
                            }
                        }
                    }
                }
                return $result;
            }
        }
        return [];
    }

    /**
     * Fetch one value from result
     * @param mixed $sql
     * @return mixed
     */
    public function fetchOne($sql)
    {
        if(!$this->inited){
            $this->init();
        }

        $statement = $this->adapter->createStatement();
        $statement->setSQl($sql);
        $statement->prepare();

        $result = $statement->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet(ResultSet::TYPE_ARRAY);
            $resultSet->initialize($result);
            /**
             * @var array $result
             */
            $result = $resultSet->current();
            if(!empty($result)){
                return  array_values($result)[0];
            }
        }
        return null;
    }

    /**
     * Execute query
     * @param mixed  $sql
     * @return void
     */
    public function query($sql) : void
    {
        if(!$this->inited){
            $this->init();
        }
        $this->adapter->query($sql, Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
    }

    /**
     * Fetch row from result set
     * @param mixed $sql
     * @return array
     */
    public function fetchRow($sql) : array
    {
        if(!$this->inited){
            $this->init();
        }

        $statement = $this->adapter->createStatement();
        $statement->setSQl($sql);
        $statement->prepare();

        $result = $statement->execute();
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet(ResultSet::TYPE_ARRAY);
            $resultSet->initialize($result);
            /**
             * @var array $resultData
             */
            $resultData = $resultSet->current();
            if(empty($resultData)){
                $resultData = [];
            }
            return $resultData;
        }
        return [];
    }

    /**
     * Quote table identifier
     * @param string $string
     * @return string
     */
    public function quoteIdentifier(string $string) : string
    {
        if(!$this->inited){
            $this->init();
        }

        if($this->adapter->getPlatform()->getName() === 'MySQL'){
            return '`'.str_replace(['`','.'], ['','`.`'], $string).'`';
        }else{
            return $this->adapter->getPlatform()->quoteIdentifier($string);
        }
    }

    /**
     * Quote value
     * @param mixed $value
     * @return string
     */
    public function quote($value) : string
    {
        if(!$this->inited){
            $this->init();
        }

        return $this->adapter->getPlatform()->quoteValue($value);
    }

    /**
     * Get adapter config
     * @return array
     */
    public function getConfig() : array
    {
        return $this->params;
    }

    /**
     * Get list of DB tables names
     * @return string[]
     */
    public function listTables()
    {
        if(!$this->inited){
            $this->init();
        }

        $metadata = new Db\Metadata\Metadata($this->adapter);
        return $metadata->getTableNames();
    }

    /**
     * Get metadata object for current DB connection
     * @return Metadata
     */
    public function getMeta() : Metadata
    {
        if(!$this->inited){
            $this->init();
        }
        return new Metadata($this->adapter);
    }

    /**
     * Start transaction
     * @return void
     */
    public function beginTransaction() : void
    {
        if(!$this->inited){
            $this->init();
        }
        $this->adapter->getDriver()->getConnection()->beginTransaction();
    }

    /**
     * Rollback transaction
     * @return void
     */
    public function rollback() : void
    {
        if(!$this->inited){
            $this->init();
        }
        $this->adapter->getDriver()->getConnection()->rollback();
    }

    /**
     * @return void
     */
    public function commit() : void
    {
        if(!$this->inited){
            $this->init();
        }
        $this->adapter->getDriver()->getConnection()->commit();
    }

    /**
     * Fix for mysqli driver
     * convert bool into integer
     * @param array $values
     * @return array
     */
    protected function convertBooleanValues(array $values) : array
    {
        foreach ($values as &$value){
            if(is_bool($value)){
                $value = intval($value);
            }
        }
        return $values;
    }

    /**
     * Insert record
     * @param string $table
     * @param array $values
     * @return void
     */
    public function insert(string $table , array $values) : void
    {
        if(!empty($values) && $this->params['adapter'] === 'Mysqli'){
            $values = $this->convertBooleanValues($values);
        }

        $sql = $this->sql();
        $insert = $sql->insert($table);
        $insert->values($values);

        $statement = $sql->prepareStatementForSqlObject($insert);
        $statement->execute();
    }

    /**
     * Delete records from table using where condition
     * @param string $table
     * @param string|null $where
     * @return void
     */
    public function delete(string $table, ?string $where = null) : void
    {
        $sql = $this->sql();
        $delete = $sql->delete($table);

        if(!empty($where)){
            $delete->where($where);
        }

        $statement = $sql->prepareStatementForSqlObject($delete);
        $statement->execute();
    }

    /**
     * Update records using where condition
     * @param string $table
     * @param array $values
     * @param string|null $where
     * @return void
     */
    public function update(string $table, array $values, $where = null ) : void
    {
        if(!empty($values) && $this->params['adapter'] === 'Mysqli'){
            $values = $this->convertBooleanValues($values);
        }

        $sql = $this->sql();
        $update = $sql->update($table);
        $update->set($values);

        if(!empty($where)){
            $update->where($where);
        }

        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    }

    /**
     * Get last insert ID
     * @param string|null $tableName
     * @param string|null $primaryKey
     * @return mixed
     */
    public function lastInsertId($tableName = null, $primaryKey = null)
    {
        if(!$this->inited){
            $this->init();
        }
        return $this->adapter->getDriver()->getLastGeneratedValue();
    }

    /**
     * @deprecated
     * @param array $values
     * @return string
     */
    public function quoteValueList(array $values) : string
    {
        if(!$this->inited){
            $this->init();
        }
        return $this->adapter->getPlatform()->quoteValueList($values);
    }

    /**
     * Add listener
     * @param int $eventCode
     * @param callable $listener
     */
    public function on(int $eventCode, callable $listener) : void
    {
        if(!isset($this->listeners[$eventCode])){
            $this->listeners[$eventCode] = [];
        }
        $this->listeners[$eventCode][] = $listener;
    }

    /**
     * @param int $eventCode
     * @param array|null $data
     */
    protected function fireEvent(int $eventCode , ?array $data = []) : void
    {
        /**
         * @var array $data
         */
        if(isset($this->listeners[$eventCode])){
            foreach ($this->listeners[$eventCode] as $listener){
                /**
                 * @var callable $listener
                 */
                $listener(new Adapter\Event($eventCode , $data));
            }
        }
    }
}