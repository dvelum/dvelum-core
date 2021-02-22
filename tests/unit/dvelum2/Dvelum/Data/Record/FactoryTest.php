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
namespace Dvelum\Data\Record;

use Dvelum\Data\Record\Export\Database;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testFactory()
    {
        $factory = new Factory(\Dvelum\Config::storage()->get('data_record.php'));
        $o = $factory->create('testRecord');
        $this->assertInstanceOf(Record::class, $o);
        $this->assertTrue($o->getConfig()->fieldExists('json_field'));

        $this->expectException(\InvalidArgumentException::class);
        $factory->create('undefinedObject');
    }

    public function testGetDbExport()
    {
        $factory = new Factory(\Dvelum\Config::storage()->get('data_record.php'));
        $export = $factory->getDbExport();
        $this->assertInstanceOf(Database::class, $export);
    }

}