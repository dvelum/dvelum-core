<?php

namespace Dvelum;

use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    public function testGetExt(): void
    {
        $this->assertEquals('.php', File::getExt('index.php'));
        $this->assertEquals('.jpeg', File::getExt('1.jpeg'));
        $this->assertEquals('.dat', File::getExt('data.dat'));
        $this->assertEquals('.binary', File::getExt('.binary'));
        $this->assertEquals(false, File::getExt('binary'));
        $this->assertEquals('.php', File::getExt('Store/Session1.php'));
    }


    public function testFillEndSep(): void
    {
        $this->assertEquals('/path/to/file/', File::fillEndSep('/path/to/file'));
        $this->assertEquals('/path/to/file/', File::fillEndSep('/path/to/file/'));
    }


    public function testRmdirRecursive(): void
    {
        $tmpDir = Config::storage()->get('main.php')->get('tmp');
        $dir = $tmpDir . 'unit/' . date('Y') . '/' . date('m') . '/' . date('d');
        $this->assertTrue(mkdir($dir, 0775, true));
        $this->assertTrue((bool)file_put_contents($dir . '/test.txt', 'test'));
        $this->assertTrue(File::rmdirRecursive($tmpDir . 'unit/' . date('Y'), true));
        $this->assertTrue(!file_exists($tmpDir . 'unit/' . date('Y')));
        File::rmdirRecursive($tmpDir . 'unit/', true);
    }
}
