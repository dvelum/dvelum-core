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

use Dvelum\Resource;
use PHPUnit\Framework\TestCase;

class ResourceTest extends TestCase
{
    private function createResource(): Resource
    {
        return new Resource([
            'jsCacheUrl' => '/js/cache/',
            'jsCachePath' => 'www/js/cache/',
            'cssCacheUrl' => '/css/cache/',
            'cssCachePath' => 'www/css/cache/',
            'wwwRoot' => '/',
            'wwwPath' => 'www',
        ]);
    }
    public function testAddJs() : void
    {
        $resource = $this->createResource();
        $resource->addJs('/app/system/common.js', false, false, 'head_test');
        $resource->addJsRawFile('/app/system/Application.js');
        $result = $resource->includeJs(false, false, 'head_test');
        $result2 = $resource->includeJs(false, false);
        $this->assertEquals('<script type="text/javascript" src="/app/system/common.js"></script>', trim($result));
        $this->assertEquals(
            '<script type="text/javascript" src="/app/system/Application.js"></script>',
            trim($result2)
        );
    }

    public function testGetInlineJS() : void
    {
        $resource = $this->createResource();
        $resource->addRawJs('var a = 1;');
        $this->assertEquals('var a = 1;', $resource->getInlineJs());
        $resource->cleanInlineJs();
        $this->assertEquals('', $resource->getInlineJs());
    }

    public function testCacheJs() : void
    {
        $resource = $this->createResource();
        $code = 'var a=7;';
        $filePath = str_replace('//', '/', ($resource->getConfig()['wwwPath'] . $resource->cacheJs($code)));
        $this->assertTrue(file_exists($filePath));
        $this->assertEquals('var a=7;', file_get_contents($filePath));
    }
}
