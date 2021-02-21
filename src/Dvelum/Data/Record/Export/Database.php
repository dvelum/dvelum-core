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

namespace Dvelum\Data\Record\Export;

use Dvelum\Data\Record\Record;
use Dvelum\Data\Record\Config;

class Database
{
    /**
     * @param Record $record
     * @return array
     * @throws \JsonException
     */
    public function exportRecord(Record $record) : array
    {
        return $this->convertData($record->getConfig(), $record->getData());
    }

    /**
     * @param Record $record
     * @return array
     * @throws \JsonException
     */
    public function exportUpdates(Record $record) : array
    {
        return $this->convertData($record->getConfig(), $record->getUpdates());
    }

    /**
     * @param Config $config
     * @param array $data
     * @return array
     * @throws \JsonException
     */
    private function convertData(Config $config, array $data) : array
    {
        $fields = $config->getFields();
        foreach ($fields as $name => $field){
            if($field->getType() === 'json'){
                if(!empty($data[$name])){
                    $data[$name] = json_encode($data[$name],JSON_THROW_ON_ERROR);
                }
            }
        }
        return $data;
    }
}