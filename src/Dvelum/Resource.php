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

namespace Dvelum;

use Dvelum\Cache\CacheInterface;
use Dvelum\Config\ConfigInterface;

/**
 * Resources Class
 * Used for work with JS and CSS sources
 * @author Kirill A Egorov 2011
 */
class Resource
{
    /**
     * @var Config\ConfigInterface
     */
    protected $config;

    /**
     * @var CacheInterface|false
     */
    protected $cache = false;
    /**
     * @var array
     */
    protected $jsFiles = [];
    /**
     * @var array
     */
    protected $rawFiles = [];
    /**
     * @var array
     */
    protected $cssFiles = [];
    /**
     * @var string
     */
    protected $rawJs = '';
    /**
     * @var string
     */
    protected $rawCss = '';
    /**
     * @var string
     */
    protected $inlineJs = '';

    /**
     * @return self
     */
    static public function factory() : self
    {
        static $instance = null;

        if(empty($instance)){
            $instance = new static();
        }

        return $instance;
    }

    /**
     * Set configuration options
     * @param ConfigInterface $config
     * @return void
     * @throws \Exception
     */
    public function setConfig(ConfigInterface $config) : void
    {
        $this->config = $config;
        if($this->config->offsetExists('cache')){
            $this->cache = $this->config->get('cache');
        }
    }

    protected function __construct(){}

    /**
     * Add javascript file to the contentent
     *
     * @param string $file- file path relate to document root
     * @param mixed $order  - include order
     * @param boolean $minified - file already minified
     * @param string|bool $tag
     * @return void
     */
    public function addJs(string $file, $order = false, $minified = false, $tag = false) : void
    {
        if ($file[0] === '/')
            $file = substr($file, 1);

        $hash = md5($file);

        if($order === false)
            $order = sizeof($this->jsFiles);

        if(!isset($this->jsFiles[$hash]))
        {
            $item =  new \stdClass();
            $item->file = $file;
            $item->order = $order;
            $item->tag = $tag;
            $item->minified = $minified;
            $this->jsFiles[$hash] = $item;
        }
    }

    /**
     * Add css file to the content
     * @param string $file
     * @param mixed $order
     * @return void
     */
    public function addCss(string $file , $order = false) : void
    {
        if($file[0] === '/')
            $file = substr($file, 1);

        $hash = md5($file);

        if($order === false){
            $order = sizeof($this->cssFiles);
        }

        if(!isset($this->cssFiles[$hash]))
        {
            $item =  new \stdClass();
            $item->file = $file;
            $item->order = $order;
            $this->cssFiles[$hash] = $item;
        }
    }

    /**
     * Add Java Script code
     * (will be minified and cached)
     * @param string $script
     * @return void
     */
    public function addRawJs(string $script) : void
    {
        $this->rawJs .= $script;
    }

    /**
     * Add standalone JS file (no modifications)
     * @param string $file - file path relative to the document root directory
     * @return void
     */
    public function addJsRawFile(string $file) : void
    {
        if($file[0] === '/')
            $file = substr($file, 1);

        if(! in_array($file , $this->rawFiles , true))
            $this->rawFiles[] = $file;
    }

    /**
     * Add inline Java Script code
     * @param string $script
     * @return void
     */
    public function addInlineJs(string $script) : void
    {
        $this->inlineJs.= $script;
    }

    /**
     * Add inline css syles
     * @param string $css
     * @return void
     */
    public function addRawCss(string $css) : void
    {
        $this->rawCss.= $css;
    }

    /**
     * Include JS resources by tag
     * @param bool $useMin
     * @param bool$compile
     * @param mixed $tag
     * @return string
     */
    public function includeJsByTag($useMin = false , $compile = false , $tag = false) : string
    {
        $s = '';
        $fileList = $this->jsFiles;

        foreach ($fileList as $k=>$v){
            if($v->tag != $tag){
                unset($fileList[$k]);
            }
        }

        /*
         * javascript files
         */
        if(!empty($fileList))
        {
            $fileList = Utils::sortByProperty($fileList, 'order');
            if($compile)
                $s .= '<script type="text/javascript" src="' .  $this->config->get('wwwRoot') . $this->compileJsFiles($fileList , $useMin) . '"></script>' . "\n";
            else
                foreach($fileList as $file)
                    $s .= '<script type="text/javascript" src="' .   $this->config->get('wwwRoot') . $file->file . '"></script>' . "\n";
        }

        return $s;
    }

    /**
     * Returns javascript source tags. Include order: Files , Raw , Inline
     * @param boolean $useMin - use Js minify
     * @param boolean $compile - compile Files into one
     * @param mixed $tag
     * @return string
     */
    public function includeJs($useMin = false , $compile = false , $tag = false) : string
    {
        $fileList = $this->jsFiles;

        foreach ($fileList as $k=>$v){
            if($v->tag !== $tag){
                unset($fileList[$k]);
            }
        }

        $s = '';
        /*
         * Raw files
         */
        if(!empty($this->rawFiles) && empty($tag)){
            foreach($this->rawFiles as $file){
                if(strpos($file,'http')===0){
                    $s .= '<script type="text/javascript" src="' . $file . '"></script>' . "\n";
                }else{
                    $s .= '<script type="text/javascript" src="' . $this->config->get('wwwRoot') . $file . '"></script>' . "\n";
                }
            }
        }

        $s .=  $this->includeJsByTag($useMin , $compile , $tag);
        /*
         * Raw javascript
         */
        if(strlen($this->rawJs))
        {
            $s .= '<script type="text/javascript" src="' . $this->cacheJs($this->rawJs) . '"></script>' . "\n";
        }
        /*
         * Inline javascript
         */
        if(!empty($this->inlineJs))
        {
            // it's too expensive
            //if($useMin)
            //	$this->inlineJs = Code_Js_Minify::minify($this->inlineJs);
            $s .= '<script type="text/javascript">' . "\n" . $this->inlineJs . "\n" . ' </script>' . "\n";
        }
        return $s;
    }

    /**
     * Create cache file for JS code
     * @param string $code
     * @param bool $minify, optional default false
     * @return string - file url
     */
    public function cacheJs(string $code , bool $minify = false) : string
    {
        $hash = md5($code);
        $cacheFile = $hash . '.js';
        $cacheFile = Utils::createCachePath( $this->config->get('jsCachePath') , $cacheFile);

        if(!file_exists($cacheFile))
        {
            if($minify)
                $code = \Dvelum\App\Code\Minify\Minify::factory()->minifyJs($code);

            file_put_contents($cacheFile, $code);
        }

        return str_replace($this->config->get('jsCachePath'), $this->config->get('wwwRoot'). $this->config->get('jsCacheUrl'), $cacheFile);
    }

    /**
     * Compile JS files cache
     * @param array $files - file paths relative to the document root directory
     * @param boolean $minify - minify scripts
     * @return string  - cached file path
     */
    protected function compileJsFiles(array $files , bool $minify /*deprecated*/) : string
    {
        $validHash = $this->getFileHash(Utils::fetchCol('file' , $files));

        $cacheFile = Utils::createCachePath($this->config->get('jsCachePath'), $validHash . '.js');
        $cachedUrl = \str_replace($this->config->get('jsCachePath'), $this->config->get('jsCacheUrl') , $cacheFile);

        if(!file_exists($cacheFile)){
            $src = '';
            $minify =  \Dvelum\App\Code\Minify\Minify::factory();
            foreach($files as $v){
                if($v->minified){
                    $src.="\n/**/\n".file_get_contents($this->config->get('wwwPath') . $v->file);
                }else{
                    $file =  $v->file;
                    $paramsPos = strpos($file , '?');
                    if($paramsPos!==false) {
                        $file = substr($file, 0 , $paramsPos);
                    }
                    $src.="\n/**/\n".$minify->minifyJs($this->config->get('wwwPath') . $file);
                }
            }
            file_put_contents($cacheFile,$src);
        }
        return $cachedUrl;
    }

    /**
     * Get a hash for the file list. Used to check for changes in files.
     * @param array $files - File paths relative to the document root directory
     * @return string
     */
    public function getFileHash(array $files)
    {
        $listHash = \md5(\serialize($files));
        /**
         * Checking if hash is cached
         * (IO operations is too expensive)
         */
        if(!empty($this->cache))
        {
            $dataHash = $this->cache->load($listHash);
            if($dataHash)
                return $dataHash;
        }

        $dataHash = '';
        foreach($files as $file)
        {
            $paramsPos = strpos($file , '?');
            if($paramsPos!==false) {
                $file = substr($file, 0 , $paramsPos);
            }
            $dataHash .= $file . ':' . filemtime( $this->config->get('wwwPath') . $file);
        }

        if($this->cache)
            $this->cache->save(\md5($dataHash), $listHash, 60);

        return \md5($dataHash);
    }

    /**
     * Get html code for css files include
     * @param bool $combine
     * @return string
     */
    public function includeCss($combine = false) : string
    {
        $s = '';

        if(!empty($this->cssFiles))
        {
            $this->cssFiles = Utils::sortByProperty($this->cssFiles, 'order');

            if($combine)
            {
                $fileList = [];
                foreach($this->cssFiles as $k => $v){
                    $fileList[] = $v->file;
                }
                $validHash = $this->getFileHash($fileList);
                $cacheFile = Utils::createCachePath($this->config->get('cssCachePath'), $validHash . '.css');
                $cachedUrl = \str_replace($this->config->get('cssCachePath'), $this->config->get('cssCacheUrl') , $cacheFile);

                if(!file_exists($cacheFile)){
                    foreach($fileList as &$v){
                        $paramsPos = strpos($v , '?');
                        if($paramsPos!==false) {
                            $v = substr($v, 0 , $paramsPos);
                        }
                        $v = $this->config->get('wwwPath') . $v;
                    }unset($v);
                    \Dvelum\App\Code\Minify\Minify::factory()->minifyCssFiles($fileList, $cacheFile);
                }
                $s .= '<link rel="stylesheet" type="text/css" href="' . $this->config->get('wwwRoot') . $cachedUrl . '" />' . "\n";
            }else{
                foreach($this->cssFiles as $k => $v){

                    if($v->file[0] === '/'){
                        $fPath = substr($v->file,1);
                    }else{
                        $fPath = $v->file;
                    }

                    $paramsPos = strpos($v->file , '?');
                    if($paramsPos === false) {
                        $mTimeParam = '?m='.filemtime($this->config->get('wwwPath') . $v->file);
                    }else{
                        $mTimeParam='';
                    }

                    $s .= '<link rel="stylesheet" type="text/css" href="' . $this->config->get('wwwRoot') . $fPath . $mTimeParam.'" />' . "\n";
                }
            }
        }

        if(strlen($this->rawCss))
            $s .= '<style type="text/css">' . "\n" . $this->rawCss . "\n" . '</style>' . "\n";

        return $s;
    }

    /**
     * Get raw JS code
     * @return string
     */
    public function getInlineJs() : string
    {
        return $this->rawJs;
    }

    /**
     * Clean raw js
     * @return void
     */
    public function cleanInlineJs() : void
    {
        $this->rawJs = '';
    }

    /**
     * @return ConfigInterface|null
     */
    public function getConfig() : ?ConfigInterface
    {
        return $this->config;
    }
}