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

namespace Dvelum;

use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testGetPart(): void
    {
        $request = new Request();
        $request->setUri('/test/request/uri');
        $this->assertEquals('/test/request/uri', $request->getUri());
        $this->assertEquals('test', $request->getPart(0));
        $this->assertEquals('request', $request->getPart(1));
        $this->assertEquals('uri', $request->getPart(2));
        $this->assertEquals(null, $request->getPart(4));
    }

    public function testGet(): void
    {
        $request = new Request();
        $_GET['param'] = 'value';
        $request->updateGet('param', 'value');
        $request->updateGet('param3', '1');
        $this->assertEquals('value', $request->get('param', 'string', ''));
        $this->assertEquals(null, $request->get('param2', 'string', null));
        $this->assertEquals(1, $request->get('param3', 'int', null));
    }

    public function testPost(): void
    {
        $request = new Request();
        $request->updatePost('param', 'value');
        $request->updatePost('param3', '1');
        $this->assertEquals('value', $request->post('param', 'string', ''));
        $this->assertEquals(null, $request->post('param2', 'string', null));
        $this->assertEquals(1, $request->post('param3', 'int', null));
        $this->assertTrue($request->hasPost());
    }

    public function testSetPost(): void
    {
        $request = new Request();
        $request->setPostParams(['param1' => 'val1', 'param2' => 'val2']);
        $this->assertEquals(['param1' => 'val1', 'param2' => 'val2'], $request->postArray());
    }

    public function testSetGet(): void
    {
        $request = new Request();
        $request->setGetParams(['param' => 'val1', 'param2' => 'val2']);
        $this->assertEquals(['param' => 'val1', 'param2' => 'val2'], $request->getArray());
    }

    public function testUrl(): void
    {
        $request = new Request();
        $this->assertEquals('/my/path', $request->url(['my', 'path']));
    }

    public function testSetUri(): void
    {
        $request = new Request();
        $request->setUri('/news.html?a=b&d=8345');
        $this->assertEquals($request->getUri(), '/news');
    }

    public function testGetArray(): void
    {
        $request = new Request();
        $request->updateGet('key', 'val');
        $actual = $request->getArray();
        $this->assertEquals('val', $actual['key']);
    }

    public function testPostArray(): void
    {
        $request = new Request();
        $request->updatePost('key', 'val');
        $this->assertEquals($request->postArray(), array('key' => 'val'));
    }

    public function testUpdatePost(): void
    {
        $request = new Request();
        $request->updatePost('key', 'val');
        $this->assertEquals($request->post('key', 'string', false), 'val');
        $this->assertEquals($request->post('key3', 'string', false), false);
    }

    public function testUpdateGet(): void
    {
        $request = new Request();
        $request->updateGet('key', 'val');
        $this->assertEquals($request->get('key', 'string', false), 'val');
        $this->assertEquals($request->get('key3', 'string', false), false);
    }

    public function testIsAjax(): void
    {
        $request = new Request();
        $this->assertEquals($request->isAjax(), false);
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->assertEquals($request->isAjax(), true);
    }

    public function testHasPost(): void
    {
        $request = new Request();
        $post = $request->postArray();
        $this->assertEquals(!empty($post), $request->hasPost());

        $request->updatePost('key', 'value');

        $post = $request->postArray();
        $this->assertEquals(!empty($post), $request->hasPost());
    }
}
