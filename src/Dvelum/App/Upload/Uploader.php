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

namespace Dvelum\App\Upload;

use Dvelum\App\Upload\Adapter\AbstractAdapter;
use Dvelum\App\Upload\Adapter\File;
use Dvelum\App\Upload\Adapter\Image;

/**
 * File Uploader
 * @author Kirill Egorov 2010
 */
class Uploader
{
    /**
     * @var array<string,mixed>
     */
    protected array $config;
    /**
     * @var AbstractAdapter[] $uploaders
     */
    protected array $uploaders;
    /**
     * @var array<int,string>
     */
    protected array $errors = [];

    /**
     * Uploader constructor.
     * @param array<string,mixed> $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->uploaders = [];
    }

    /**
     * Auto create dirs for upload
     * @param string $root
     * @param string $path
     * @return bool
     */
    public function createDirs(string $root, string $path): bool
    {
        $path = str_replace('//', '/', $root . '/' . $path);

        if (file_exists($path)) {
            return true;
        }

        if (!@mkdir($path, 0775, true)) {
            return false;
        }

        return true;
    }

    /**
     * Identify file type
     * @param string $extension
     * @return string|false
     */
    protected function identifyType(string $extension)
    {
        foreach ($this->config as $k => $v) {
            if (in_array($extension, $v['extensions'], true)) {
                return $k;
            }
        }

        return false;
    }

    /**
     * Multiple upload files
     *
     * @param array<string,mixed> $files - array of Request::files() items
     * @param string $path
     * @param bool $formUpload - optional, default true
     * @return array<int,array> - uploaded files Info on error
     */
    public function start(array $files, string $path, bool $formUpload = true): array
    {
        $this->errors = [];

        $uploadedFiles = [];
        foreach ($files as $item) {
            if (isset($item['error']) && $item['error']) {
                $this->errors[] = 'Server upload error';
                continue;
            }

            $item['name'] = str_replace(' ', '_', $item['name']);
            $item['name'] = strtolower((string)preg_replace("/[^A-Za-z0-9_\-\.]/i", '', (string)$item['name']));

            $item['ext'] = \Dvelum\File::getExt($item['name']);
            $item['title'] = str_replace($item['ext'], '', $item['name']);
            $type = $this->identifyType($item['ext']);

            if (!$type) {
                continue;
            }

            switch ($type) {
                case 'image':
                    if (!isset($this->uploaders['image'])) {
                        $this->uploaders['image'] = new Image($this->config['image']);
                    }
                    /**
                     * @var AbstractAdapter $uploader
                     */
                    $uploader = $this->uploaders['image'];

                    $file = $uploader->upload($item, $path, $formUpload);

                    if (!empty($file)) {
                        $file['type'] = $type;
                        $file['title'] = $item['title'];
                        if (isset($item['old_name'])) {
                            $file['old_name'] = $item['old_name'];
                        } else {
                            $file['old_name'] = $item['name'];
                        }
                        $uploadedFiles[] = $file;
                    } else {
                        if (!empty($uploader->getError())) {
                            $this->errors[] = $uploader->getError();
                        }
                    }
                    break;

                case 'audio':
                case 'video':
                case 'file':
                    if (!isset($this->uploaders['file'])) {
                        $this->uploaders['file'] = new File($this->config[$type]);
                    }
                    /**
                     * @var AbstractAdapter $uploader
                     */
                    $uploader = $this->uploaders['file'];
                    $file = $uploader->upload($item, $path, $formUpload);

                    if (!empty($file)) {
                        $file['type'] = $type;
                        $file['title'] = $item['title'];

                        if (isset($item['old_name'])) {
                            $file['old_name'] = $item['old_name'];
                        } else {
                            $file['old_name'] = $item['name'];
                        }
                        $uploadedFiles[] = $file;
                    } else {
                        if (!empty($uploader->getError())) {
                            $this->errors[] = $uploader->getError();
                        }
                    }
                    break;
            }
        }

        return $uploadedFiles;
    }

    /**
     * Get upload errors
     * @return array<int,string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
