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

namespace Dvelum\App\Upload\Adapter;

/**
 * File uploader class
 */
class File extends AbstractAdapter
{
    /**
     * Upload file
     * @param array<string,mixed> $data $_FILES array item
     * @param bool $formUpload - optional, default true
     * @phpstan-return array<string,mixed>|false on error
     * @return array{name:string,path:string,type:string,size:int}
     */
    public function upload(array $data, string $path, bool $formUpload = true)
    {
        $this->error = '';

        if ($data['error']) {
            $this->error = 'Server upload error';
            return false;
        }

        if (isset($this->config['max_file_size']) && ($this->config['max_file_size'])) {
            if ($data['size'] > $this->config['max_file_size']) {
                $this->error = 'File too large. Check max_file_size option';
                return false;
            }
        }

        $result = array(
            'name' => '',
            'path' => '',
            'size' => '',
            'type' => ''
        );

        $name = $this->createUploadedName($data);

        if (empty($name)) {
            return false;
        }

        $ext = \Dvelum\File::getExt((string)$name);

        if (!in_array($ext, $this->config['extensions'])) {
            $this->error = 'File extension is not allowed';
            return false;
        }


        $namePart = str_replace($ext, '', (string)$name);

        if (isset($this->config['rewrite']) && $this->config['rewrite']) {
            if (file_exists($path . $namePart . $ext)) {
                @unlink($path . $namePart . $ext);
            }
        }

        if (file_exists($path . $namePart . $ext)) {
            $namePart .= '-0';
        }

        $renameCount = 0;

        while (file_exists($path . $namePart . $ext)) {
            $parts = explode('-', $namePart);
            $el = array_pop($parts);
            $el = (int)($el);
            $el++;
            $parts[] = $el;
            $namePart = implode('-', $parts);
            $renameCount++;
            // limit iterations
            if ($renameCount == 100) {
                $this->error = 'Cannot rename file. Iterations limit';
                return false;
            }
        }

        $result['name'] = $namePart . $ext;
        $result['path'] = $path . $namePart . $ext;
        $result['ext'] = $ext;

        if ($formUpload) {
            if (!move_uploaded_file($data['tmp_name'], $result['path'])) {
                $this->error = 'move_uploaded_file error';
                return false;
            }
        } else {
            if (!copy($data['tmp_name'], $result['path'])) {
                $this->error = 'copy error';
                return false;
            }
        }

        $result['size'] = $data['size'];
        $result['type'] = $data['type'];

        @chmod($result['path'], 0644);

        return $result;
    }

    /**
     * Create filename for uploaded file
     * @param array<string,mixed> $fileData
     * @return string|null
     */
    protected function createUploadedName(array $fileData): ?string
    {
        $name = str_replace(' ', '_', $fileData['name']);
        $name = preg_replace("/[^A-Za-z0-9_\-\.]/i", '', $name);
        if (!is_string($name)) {
            $name = null;
        }
        return $name;
    }
}
