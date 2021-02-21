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

use PHPUnit\Framework\TestCase;

class RecordTest extends TestCase
{
    private function createConfig(): Config
    {
        return new Config(\Dvelum\Config::storage()->get('data_object/test_record.php')->__toArray());
    }

    private function createRecord(): Record
    {
        return new Record('TestRecord', $this->createConfig());
    }

    public function testNumeric()
    {
        $record = $this->createRecord();
        $record->set('int_field', 12);
        $this->assertEquals(12, $record->get('int_field'));

        $record->set('int_field', '120');
        $this->assertEquals(120, $record->get('int_field'));

        $record->set('float_field', 129.12);
        $this->assertEquals(129.12, $record->get('float_field'));

        $record->set('float_field', '120.01');
        $this->assertEquals(120.01, $record->get('float_field'));

        try {
            $record->set('float_field', 'sometext');
        } catch (\InvalidArgumentException $e) {
            return;
        }
        $this->fail('Incorrect type validation exception expected');
    }

    public function testWrongField()
    {
        $record = $this->createRecord();
        $this->expectException(\InvalidArgumentException::class);
        $record->set('undefinedField', 123);
    }

    public function testNumericLimitMax()
    {
        $record = $this->createRecord();
        $this->expectException(\InvalidArgumentException::class);
        $record->set('int_field_limit', 100);
    }

    public function testNumericLimitMin()
    {
        $record = $this->createRecord();
        $this->expectException(\InvalidArgumentException::class);
        $record->set('int_field_limit', -100);
    }

    public function testStringLimit()
    {
        $record = $this->createRecord();
        $this->expectException(\InvalidArgumentException::class);
        $record->set('string_field_limit', 'abcdefg');
    }

    public function testString()
    {
        $record = $this->createRecord();
        $record->set('string_field', 'abcdefg');
        $this->assertEquals('abcdefg', $record->get('string_field'));
        $record->set('string_field', 123);
        $this->assertEquals('123', $record->get('string_field'));
    }

    public function testDefaulDate()
    {
        $record = $this->createRecord();
        $date = date('Y-m-d H:i:s');
        $this->assertEquals($date, $record->get('string_field_date'));
    }

    public function testJson()
    {
        $record = $this->createRecord();
        $record->set('json_field', json_encode(['a' => 1, 'b' => 2]));
        $this->assertEquals(['a' => 1, 'b' => 2], $record->get('json_field'));

        $record->set('json_field', ['a' => 1, 'b' => 2]);
        $this->assertEquals(['a' => 1, 'b' => 2], $record->get('json_field'));
    }

    public function testJsonExceptionString()
    {
        $record = $this->createRecord();
        $this->expectException(\InvalidArgumentException::class);
        $record->set('json_field', json_encode('abs'));
        $this->expectException(\InvalidArgumentException::class);
        $record->set('json_field', json_encode(123));
    }
    public function testJsonExceptionNum()
    {
        $record = $this->createRecord();
        $this->expectException(\InvalidArgumentException::class);
        $record->set('json_field', json_encode(123));
    }

    public function testSetData()
    {
        $record = $this->createRecord();
        $record->setData(
            [
                'int_field' => 11,
                'float_field' => 11.2
            ]
        );
        $this->assertEquals(11, $record->get('int_field'));
        $this->assertEquals(11.2, $record->get('float_field'));
    }

    public function testValidator()
    {
        //string_field_email
        $record = $this->createRecord();
        $record->set('string_field_email', 'testmail@gmail.com');
        $this->assertEquals('testmail@gmail.com', $record->get('string_field_email'));
        $this->expectException(\InvalidArgumentException::class);
        $record->set('string_field_email', 'notmail');
    }

    public function testUndefinedField()
    {
        $record = $this->createRecord();
        $this->expectException(\InvalidArgumentException::class);
        $record->get('undefinedField');
    }

    public function testCommitChanges()
    {
        $record = $this->createRecord();
        $record->getData();
        $record->commitChanges();
        $this->assertTrue(empty($record->getUpdates()));
        $record->set('int_field', 1);
        $this->assertTrue($record->hasUpdates());
        $this->assertNotEmpty($record->getUpdates());
        $record->commitChanges();
        $this->assertEmpty($record->getUpdates());
        $this->assertEquals(1, $record->get('int_field'));
    }

    public function testDefault()
    {
        $record = $this->createRecord();
        $this->assertEquals('default', $record->get('string_default'));
    }

    public function testIsRequired()
    {
        $config = $this->createConfig();
        $this->assertTrue($config->getField('string_field_email')->isRequired());
        $this->assertFalse($config->getField('int_field')->isRequired());
    }

    public function testValidateRequired()
    {
        $record = $this->createRecord();
        $result = $record->validateRequired();
        $this->assertFalse($result->isSuccess());
        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertTrue(isset($result->getErrors()['string_field_email']));
    }
}