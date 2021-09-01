<?php

namespace Dvelum;

use PHPUnit\Framework\TestCase;

class UploadTest extends TestCase
{
    public function testCreateDirs(): void
    {
        $rootPath = '../';
        $subPath = '/uploads/0/1/2/3';
        $uploader = new \Dvelum\App\Upload\Uploader([]);
        $this->assertTrue($uploader->createDirs($rootPath, $subPath));
        $this->assertTrue(file_exists('../uploads/0/1/2/3'));
        $this->assertTrue(is_dir('../uploads/0/1/2/3'));
        File::rmdirRecursive('../uploads/', true);
    }
}
