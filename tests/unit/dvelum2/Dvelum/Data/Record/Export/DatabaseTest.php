<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2019  Kirill Yegorov
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
namespace Dvelum\Data\Record\Export;

use Dvelum\Data\Record\Factory;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    public function testExport()
    {
        $factory = new Factory(\Dvelum\Config::storage()->get('data_record.php'));
        $record = $factory->create('testRecord');
        $record->set('json_field', ['a'=>1,'b'=>2]);
        $export = new Database();
        $result = $export->exportRecord($record);
        $this->assertEquals(json_encode(['a'=>1,'b'=>2]), $result['json_field']);
    }

    public function testUpdatesExport()
    {
        $factory = new Factory(\Dvelum\Config::storage()->get('data_record.php'));
        $record = $factory->create('testRecord');
        $record->set('int_field', 1);
        $export = new Database();
        $result = $export->exportUpdates($record);
        $this->assertEquals(1, $result['int_field']);
    }
}