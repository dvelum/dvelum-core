<?php /**
 * DVelum project https://github.com/dvelum/core , https://github.com/dvelum/dvelum
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
 */ /**
 * DVelum project https://github.com/dvelum/dvelum-core , https://github.com/dvelum/dvelum
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
 */ /**
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
 */ /** @noinspection PhpMissingBreakStatementInspection */
declare(strict_types=1);

namespace Dvelum\Db\Select;

use Dvelum\Db;

class Filter
{
    const GT = '>';
    const LT = '<';
    const GT_EQ = '>=';
    const LT_EQ = '<=';
    const EQ = '=';
    const NOT_NULL = 'IS NOT NULL';
    const IS_NULL = 'IS NULL';
    const NOT = '!=';
    const IN = 'IN';
    const NOT_IN = 'NOT IN';
    const LIKE = 'LIKE';
    const NOT_LIKE = 'NOT LIKE';
    const BETWEEN = 'BETWEEN';
    const NOT_BETWEEN = 'NOT BETWEEN';
    const RAW = 'RAW';

    public $type = null;
    public $value = null;
    public $field = null;
    /**
     * @param string $field
     * @param string $type
     * @param mixed $value
     */
    public function __construct($field ,  $value = '' , $type = self::EQ)
    {
        $this->type = $type;
        $this->value = $value;
        $this->field = $field;
    }

    /**
     * Apply filter
     * @param Db\Adapter $db
     * @param Db\Select $sql
     * @throws \Exception
     */
    public function applyTo(Db\Adapter $db , $sql)
    {
        if(!($sql instanceof Db\Select))
            throw new \Exception('Db\\Select::applyTo  $sql must be instance of Db_Select/Zend_Db_Select');


        $quotedField = $db->quoteIdentifier($this->field);
        switch ($this->type)
        {
            case self::LIKE:
            case self::NOT_LIKE:
                if(is_array($this->value)) {
                    $conditions = array();
                    foreach ($this->value as $k => $v) {
                        $quotedField = $db->quoteIdentifier($k);
                        $conditions[] = $quotedField . ' LIKE ' . $db->quote('%' . $v . '%');
                    }
                    if ($this->type == self::LIKE)
                        $condition = implode(' OR ', $conditions);
                    else
                        $condition = implode(' AND ', $conditions);
                    $condition = '('.$condition.')';
                    $sql->where($condition);
                    break;
                }
            case self::LT:
            case self::GT:
            case self::EQ:
            case self::GT_EQ:
            case self::LT_EQ:
            case self::NOT:
                $sql->where($quotedField . ' ' . $this->type . ' ?' , $this->value);
                break;
            case self::IN:
            case self::NOT_IN:
                $sql->where($quotedField . ' ' . $this->type . ' (?)' , $this->value);
                break;
            case self::NOT_NULL :
            case self::IS_NULL :
                $sql->where($quotedField . ' ' . $this->type);
                break;
            case self::BETWEEN:
            case self::NOT_BETWEEN:
                $sql->where($quotedField . ' ' . $this->type . ' ' . $db->quote($this->value[0]) . ' AND ' . $db->quote($this->value[1]));
                break;
            case self::RAW:
                $sql->where($this->value);
                break;
        }
    }
}