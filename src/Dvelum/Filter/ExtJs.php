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

namespace Dvelum\Filter;

use Dvelum\Db\Select\Filter;

class ExtJs
{
    /**
     * @var array
     */
    protected $operators = [
        'gt' => Filter::GT,
        'lt' => Filter::LT,
        'like' => Filter::LIKE,
        '=' => Filter::EQ,
        'eq' => Filter::EQ,
        'on' => Filter::EQ,
        'in' => Filter::IN,
        'ne' => Filter::NOT
    ];

    /**
     * Convert filters from ExtJs UI
     * into Db\Select\Filter
     * @param array $values
     * @return Filter[]
     */
    public function toDbSelect(array $values): array
    {
        $result = [];

        foreach ($values as $item)
        {
            if (!empty($item['operator'])) {
                $operator = $item['operator'];
            } else {
                $operator = $this->operators['eq'];
            }

            $value = $item['value'];
            $field = $item['property'];

            if (!isset($this->operators[$operator])) {
                continue;
            }

            if ($operator == 'like') {
                $result[] = new Filter($field, $value . '%', $this->operators[$operator]);
            } else {
                $result[] = new Filter($field, $value, $this->operators[$operator]);
            }
        }
        return $result;
    }
}