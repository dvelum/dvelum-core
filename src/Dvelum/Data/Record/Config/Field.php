<?php
/**
 * DVelum project https://github.com/dvelum/dvelum-core , https://github.com/dvelum/dvelum
 *
 * MIT License
 *
 * Copyright (C) 2011-2021 Kirill Yegorov
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

namespace Dvelum\Data\Record\Config;
use \InvalidArgumentException;

class Field
{
    /**
     * @var array
     */
    private array $data;

    private string $name;

    /**
     * Field constructor.
     * @param string $name
     * @param array $data
     */
    public function __construct(string $name, array $data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    /**
     *  Проверить поле на обязательность
     * @return bool
     */
    public function isRequired(): bool
    {
        if (isset($this->data['required']) && $this->data['required']) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function hasDefault(): bool
    {
        return array_key_exists('default', $this->data) || isset($this->data['defaultValueAdapter']);
    }

    /**
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getDefault()
    {
        if (isset($this->data['defaultValueAdapter'])) {
            $default = (new $this->data['defaultValueAdapter'])->getValue();
        }elseif (array_key_exists('default', $this->data)){
            $default = $this->data['default'];
        }else{
            throw new InvalidArgumentException('Default value for field '.$this->getName().' is not set');
        }

        $type = $this->getType();
        if($type === 'date' || $type === 'datetime'){
            if(is_string($default)){
                $default = new \DateTime($default);
            }
        }
        return  $default;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function validateType($value) : bool
    {
        $type = $this->getType();

        if(empty($type)){
            return true;
        }

        switch ($type){
            case 'int':
            case 'float' :
                if(!is_numeric($value) && !is_null($value)){
                    return false;
                }
                break;
            case 'json':
                if(!is_string($value) && !is_array($value) && !is_null($value)){
                    return false;
                }
                break;
            case 'date':
            case 'datetime':
                if(!is_string($value) && !is_null($value) && (!$value instanceof \DateTime)){
                    return false;
                }
                break;
        }
        return true;
    }

    /**
     * @param mixed $value
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function applyType($value)
    {
        $type = $this->getType();

        if(empty($type)){
            return $value;
        }

        switch ($type){
            case 'int':
                $value = (int) $value;
                break;
            case 'string':
                $value = (string) $value;
                break;
            case 'float' :
                $value = (float) $value;
                break;
            case 'bool':
                $value = (bool) $value;
                break;
            case 'json':
                if(is_string($value)){
                    try{
                        $value =  json_decode($value, true, 512, \JSON_THROW_ON_ERROR);
                    }catch (\Exception $e){
                        throw  new InvalidArgumentException('Invalid value for json field: '. $this->getName().' '.$e->getMessage());
                    }
                }
                if(!is_array($value) && !is_null($value)){
                    throw  new InvalidArgumentException('Invalid value for json field: '. $this->getName().'. Accepts: json string(512 depth), array');
                }
                break;
            case 'date':
            case 'datetime':
                if(is_string($value)){
                    $value = new \DateTime($value);
                }

                if(!$value instanceof \DateTime && !is_null($value)){
                    throw  new InvalidArgumentException('Invalid value for datetime field: '. $this->getName().'. Accepts:\DateTime or date string');
                }
                break;
        }

        return  $value;
    }

    public function getType():?string
    {
        if(isset($this->data['type'])){
            return $this->data['type'];
        }
        return null;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function validate($value) : bool
    {
        if (isset($this->data['validator'])) {
            return (new $this->data['validator'])->validate($value);
        }

        $type = $this->getType();
        switch ($type){
            case 'int':
            case 'float':
                if(!$this->validateNumericValue($value)){
                    return false;
                }
            break;
            case 'string':
                if(!$this->validateStringValue($value)){
                    return false;
                }
                break;
            case 'date':
            case 'datetime':
                if(!$this->validateDateValue($value)){
                    return false;
                }
                break;
        }

        return true;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private function validateNumericValue($value) : bool
    {
        if(isset($this->data['minValue']) && $value < $this->data['minValue']){
            return false;
        }

        if(isset($this->data['maxValue']) && $value > $this->data['maxValue']){
            return false;
        }

        return true;
    }

    /**
     * @param string $value
     * @return bool
     */
    private function validateStringValue(string $value) : bool
    {
        $length = mb_strlen($value, 'utf-8');
        if(isset($this->data['minLength']) && $length < $this->data['minLength']){
            return false;
        }
        if(isset($this->data['maxLength']) && $length> $this->data['maxLength']){
            return false;
        }
        return true;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    private function validateDateValue($value) : bool
    {
        if(isset($this->data['minValue'])){
            if(is_string($this->data['minValue'])){
                $min = new \DateTime($this->data['minValue']);
            }else{
                $min = $this->data['minValue'];
            }
            if($min > $value){
                return false;
            }
        }

        if(isset($this->data['maxValue']) && !is_null($value)){
            if(is_string($this->data['maxValue'])){
                $max = new \DateTime($this->data['maxValue']);
            }else{
                $max = $this->data['maxValue'];
            }
            if($max < $value){
                return false;
            }
        }

        return true;
    }
}
