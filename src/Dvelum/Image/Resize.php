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
namespace Dvelum\Image;

use Dvelum\File;

/**
 * Image Resize Component
 */
class Resize
{
    const L_TYPE_H = 1;
    const L_TYPE_V = 2;
    const L_TYPE_S = 3;

    /**
     * Crop image
     * @param string  $src - source image path
     * @param string $dest - destination image path
     * @param int $x - x coord
     * @param int $y - y coord
     * @param int $w - width
     * @param int $h - height
     * @return bool
     */
    static public function cropImage($src , $dest , $x , $y , $w , $h)
    {
        $imgInfo = getimagesize($src);

        if(empty($imgInfo)){
            return false;
        }

        $img = self::createImg($src , $imgInfo[2]);
        $destImg = self::_createDuplicateLayer($imgInfo[2] , $w , $h);

        /**
         * @var resource $img
         */
        if(empty($img) || empty($destImg)){
            return false;
        }


        imagecopyresampled($destImg , $img , 0 , 0 , $x , $y , $w , $h , $w , $h);
        imagedestroy($img);

        return self::saveImage($destImg , $dest , $imgInfo[2]);
    }

    /**
     * Create image from file
     * @param string $path - file path
     * @param int $type - image type constant, source file type
     * @return resource | bool
     */
    static public function createImg($path , $type)
    {
        switch($type)
        {
            case IMAGETYPE_GIF :
                $im = imagecreatefromgif($path);
                break;
            case IMAGETYPE_JPEG :
                $im = imagecreatefromjpeg($path);
                break;
            case IMAGETYPE_PNG :
                $im = imagecreatefrompng($path);
                if(!$im){
                    return false;
                }
                imageAlphaBlending($im, true);
                imageSaveAlpha($im, true);
                break;
            default :
                trigger_error('Unsupported file type!' , E_USER_WARNING);
                return false;
        }
        return $im;
    }

    /**
     * Resize image
     * @param string $imgPath
     * @param int $width
     * @param int $height
     * @param string $newImgPath
     * @param bool $fit, optional default false
     * @param bool $crop, optional default false
     * @return bool
     */
    static public function resize($imgPath , $width , $height , $newImgPath , $fit = false , $crop = true) : bool
    {
        /*
         * Check if GD extension is loaded
         */
        if(!extension_loaded('gd') && !extension_loaded('gd2'))
        {
            trigger_error("GD is not loaded" , E_USER_WARNING);
            return false;
        }

        if($crop){
            return self::cropResize($imgPath , $width , $height , $newImgPath);
        }

        /*
         * Get Image size info
         */
        $imgInfo = getimagesize($imgPath);
        if(empty($imgInfo)){
            return false;
        }

        $im = self::createImg($imgPath , $imgInfo[2]);

        /**
         * @var resource $im
         */
        if(empty($im)){
            return false;
        }

        /*
         * If image sizes less then need just save image into the new location
         */
        if($imgInfo[0] < $width && $imgInfo[1] < $height)
        {
            $result = self::saveImage($im , $newImgPath , $imgInfo[2]);
            imagedestroy($im);
            return $result;
        }

        /*
         * Resize it, but keep it proportional
         */
        if($width / $imgInfo[0] > $height / $imgInfo[1])
        {
            $nWidth = $width;
            $nHeight = $imgInfo[1] * ($width / $imgInfo[0]);
        }
        else
        {
            $nWidth = $imgInfo[0] * ($height / $imgInfo[1]);
            $nHeight = $height;
        }

        $nWidth = round($nWidth);
        $nHeight = round($nHeight);

        if($fit)
        {

            if($nWidth > $width)
            {
                $k = $width / $nWidth;
                $nWidth = $width;
                $nHeight = $nHeight * $k;
            }

            if($nHeight > $height)
            {
                $k = $height / $nHeight;
                $nHeight = $height;
                $nWidth = $nWidth * $k;
            }
            $nWidth = round($nWidth);
            $nHeight = round($nHeight);
        }

        $newImg = self::_createDuplicateLayer($imgInfo[2] , (int) $nWidth , (int) $nHeight);
        if(empty($newImg)){
            return false;
        }

        imagecopyresampled($newImg , $im , 0 , 0 , 0 , 0 , (int) $nWidth ,(int) $nHeight , $imgInfo[0] , $imgInfo[1]);
        imagedestroy($im);

        return self::saveImage($newImg , $newImgPath , $imgInfo[2]);
    }

    /**
     * @param string $imgPath
     * @param int $width
     * @param int $height
     * @param string $newImgPath
     * @return bool
     */
    static public function resizeToFrame(string $imgPath , int $width , int $height , string $newImgPath) : bool
    {
        /*
        * Check if GD extension is loaded
        */
        if(!extension_loaded('gd') && !extension_loaded('gd2'))
        {
            trigger_error("GD is not loaded" , E_USER_WARNING);
            return false;
        }

        /*
         * Get Image size info
         */
        $imgInfo = getimagesize($imgPath);

        if(empty($imgInfo)){
            return false;
        }

        $im = self::createImg($imgPath , $imgInfo[2]);

        /**
         * @var resource $im
         */
        if(empty($im)){
            return false;
        }

        /*
        * If image sizes less then need just save image into the new location
        */
        if($imgInfo[0] < $width && $imgInfo[1] < $height) {
            $nWidth = $width;
            $nHeight = $height;
        }else{


            $nWidth = $imgInfo[0];
            $nHeight = $imgInfo[1];

            if($width < $nWidth){
                $scale = $width / $nWidth;
                $nWidth = $width;
                $nHeight = $nHeight * $scale;
            }

            if($height < $nHeight){
                $scale = $height / $nHeight;
                $nHeight = $height;
                $nWidth = $nWidth*$scale;
            }
        }

        $nWidth = round($nWidth);
        $nHeight = round($nHeight);

        $newImg = self::_createDuplicateLayer($imgInfo[2] , $width , $height);

        if(empty($newImg) || empty($im)){
            return false;
        }

        $whiteBackground = imagecolorallocatealpha($newImg , 255 , 255 , 255 , 0);
        imagefilledrectangle($newImg , 0 , 0 , $width , $height , $whiteBackground);

        $posX = ($width - $nWidth) / 2;
        $posY = ($height - $nHeight) / 2;

        imagecopyresampled($newImg , $im , (int) $posX, (int) $posY, 0 , 0 , (int)$nWidth , (int)$nHeight , $imgInfo[0] , $imgInfo[1]);
        imagedestroy($im);

        return self::saveImage($newImg , $newImgPath , $imgInfo[2]);
    }

    /**
     * Create image resource for manipulation,
     * transparent for IMG_GIF and IMG_PNG
     * @param integer $type image type
     * @param integer $width
     * @param integer $height
     * @return resource|false
     */
    static protected function _createDuplicateLayer($type , $width , $height)
    {
        $img = imagecreatetruecolor($width , $height);
        if(empty($img)){
            return false;
        }
        if(in_array($type, array(IMG_GIF, IMG_PNG, IMAGETYPE_GIF, IMAGETYPE_PNG), true))
        {
            imagealphablending($img , false);
            imagesavealpha($img , true);
            $transparent = imagecolorallocatealpha($img , 255 , 255 , 255 , 127);
            imagefilledrectangle($img , 0 , 0 , $width , $height , $transparent);
        }
        return $img;
    }

    /**
     * Save image to file
     * @param resource $resource - image resource
     * @param string $path - path to file
     * @param mixed $imgType - image type constant deprecated
     * @return boolean
     */
    static protected function saveImage($resource , $path , $imgType = false)
    {
        $ext = File::getExt(strtolower($path));
        switch ($ext){
            case '.jpg':
            case '.jpeg':
                $imgType = IMAGETYPE_JPEG;
                break;
            case '.gif':
                $imgType = IMAGETYPE_GIF;
                break;
            case 'png':
                $imgType = IMAGETYPE_PNG;
                break;
        }

        switch($imgType)
        {
            case IMAGETYPE_GIF :
                $result = imagegif($resource , $path);
                break;
            case IMAGETYPE_JPEG :
                $result = imagejpeg($resource , $path , 100);
                break;
            case IMAGETYPE_PNG :
                $result = imagepng($resource , $path);
                break;
            default :
                $result = false;
        }
        imagedestroy($resource);
        return $result;
    }

    /**
     * Detect layout orientation
     * @param integer $width
     * @param integer $height
     * @return integer - orientation constant Image_Resize::L_TYPE_S , Image_Resize::L_TYPE_H , Image_Resize::L_TYPE_V
     */
    static public function detectLayout($width , $height)
    {
        if($width == $height)
            return self::L_TYPE_S;
        elseif($width > $height)
            return self::L_TYPE_H;
        else
            return self::L_TYPE_V;
    }

    /**
     * Crop and resize image
     * @param string $imgPath
     * @param int $width
     * @param int $height
     * @param string $newImgPath
     * @return bool
     */
    static public function cropResize($imgPath , $width , $height , $newImgPath) : bool
    {
        /*
         * Get Image size info
         */
        $imgInfo = getimagesize($imgPath);
        if(empty($imgInfo)){
            return false;
        }
        $sourceWidth = $imgInfo[0];
        $sourceHeight = $imgInfo[1];

        $sourceLayout = self::detectLayout($sourceWidth , $sourceHeight);
        $resultLayout = self::detectLayout($width , $height);

        if($sourceLayout == self::L_TYPE_H && $resultLayout == self::L_TYPE_H)
        {
            $newSizes = self::_calcHorizontalToHorizontal($sourceWidth , $sourceHeight , $width , $height);
        }
        elseif($sourceLayout == self::L_TYPE_H && $resultLayout == self::L_TYPE_V)
        {
            $newSizes = self::_calcHorizontalToVertical($sourceHeight , $width , $height);
        }
        elseif($sourceLayout == self::L_TYPE_V && $resultLayout == self::L_TYPE_H)
        {
            $newSizes = self::_calcVerticalToHorizontal($sourceWidth , $width , $height);
        }
        elseif($sourceLayout == self::L_TYPE_S && $resultLayout == self::L_TYPE_S)
        {
            $newSizes = self::_calcSquareToSquare($sourceWidth , $sourceHeight);
        }
        else
        {
            /*
             * Vertical to vertical
             * sqrt to sqrt
             * vertical to sqrt
             * horizontal to sqrt
             * sqrt to vertical
             * sqrt to horizontal
             */
            $newSizes = self::_calcHorizontalToHorizontal($sourceWidth , $sourceHeight , $width , $height);
        }

        $x = 0;
        $y = 0;

        if($newSizes[0] < $sourceWidth)
        {
            $x = ($sourceWidth - $newSizes[0]) / 2;
        }

        if($newSizes[1] < $sourceHeight)
        {
            $difference = $sourceHeight - $newSizes[1];
            $y = $difference / 2 - $difference / 4;
        }

        $im = self::createImg($imgPath , $imgInfo[2]);
        $dest = self::_createDuplicateLayer($imgInfo[2] , (int) $width , (int) $height);
        /**
         * @var resource $im
         */
        if(empty($im) || empty($dest)){
            return false;
        }

        imagecopyresampled($dest , $im , 0 , 0 , (int) $x , (int) $y , (int) $width , (int) $height , (int) $newSizes[0] , (int) $newSizes[1]);
        imagedestroy($im);
        self::saveImage($dest , $newImgPath , $imgInfo[2]);
        return true;
    }

    /**
     * @param int $sourceWidth
     * @param int $sourceHeight
     * @param int $width
     * @param int $height
     * @return array
     */
    static protected function _calcHorizontalToHorizontal($sourceWidth , $sourceHeight , $width , $height) : array
    {
        $sourceProportion = $sourceWidth / $sourceHeight;
        $proportion = $width / $height;

        if($sourceProportion > $proportion)
            return self::_calcHorizontalToVertical($sourceHeight , $width , $height);
        else
            return self::_calcVerticalToHorizontal($sourceWidth , $width , $height);
    }

    /**
     * @param int $sourceHeight
     * @param int $width
     * @param int $height
     * @return array
     */
    static protected function _calcHorizontalToVertical(int $sourceHeight , int $width , int $height) : array
    {
        $proportion = $width / $height;

        $newHeight = $sourceHeight;
        $newWidth = $newHeight * $proportion;

        return [
            $newWidth,
            $newHeight
        ];
    }

    /**
     * @param int $sourceWidth
     * @param int $width
     * @param int $height
     * @return array
     */
    static protected function _calcVerticalToHorizontal($sourceWidth , $width , $height) : array
    {
        $proportion = $width / $height;
        $newWidth = $sourceWidth;
        $newHeight = $newWidth / $proportion;

        return [
            intval($newWidth),
            intval($newHeight)
        ];
    }

    /**
     * @param int $sourceWidth
     * @param int $sourceHeight
     * @return array
     */
    static protected function _calcSquareToSquare(int $sourceWidth , int $sourceHeight) : array
    {
        return [
            $sourceWidth,
            $sourceHeight
        ];
    }
}