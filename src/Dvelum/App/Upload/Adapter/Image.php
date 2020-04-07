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

use Dvelum\Image\Resize;

/**
 * Image Uploader
 */
class Image extends File
{

    /**
     * Create filename for uploaded file
     * @param array $fileData
     * @return string|null
     */
    protected function createUploadedName(array $fileData) : ?string
    {
        $name = str_replace(' ' , '_' , $fileData['name']);
        $name = preg_replace("/[^A-Za-z0-9_\-\.]/i" , '' , $name);
        $info = getimagesize($fileData['tmp_name']);

        if(!isset($info[2])){
            return null;
        }
        $ext = \Dvelum\File::getExt($name);
        $name = str_replace($ext,'', $name);
        // fix file extension from image type
        $ext = image_type_to_extension($info[2]);
        return  $name . $ext;
    }
    /**
     * @inheritDoc
     */
    public function upload(array $data, string $path, bool $formUpload = true)
    {
        $data = parent::upload($data, $path, $formUpload);
        if (!empty($data) && !empty($this->config['sizes'])) {
            foreach ($this->config['sizes'] as $name => $xy) {
                $ext = \Dvelum\File::getExt($data['path']);
                $replace = '-' . $name . $ext;
                $newName = str_replace($ext, ($replace), $data['path']);

                switch ($this->config['thumb_types'][$name]) {
                    case 'crop' :
                        Resize::resize($data['path'], $xy[0], $xy[1], $newName, true, true);
                        break;
                    case 'resize_fit':
                        Resize::resize($data['path'], $xy[0], $xy[1], $newName, true, false);
                        break;
                    case 'resize':
                        Resize::resize($data['path'], $xy[0], $xy[1], $newName, false, false);
                        break;
                    case 'resize_to_frame':
                        Resize::resizeToFrame($data['path'], $xy[0], $xy[1], $newName);
                        break;
                }
                if ($name == 'icon') {
                    $data['thumb'] = $newName;
                }
            }
        }
        return $data;
    }
}