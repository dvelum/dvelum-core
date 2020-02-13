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

namespace Dvelum\App\Router;

use Dvelum\App\Router;
use Dvelum\Config;
use Dvelum\Filter;
use Dvelum\Request;
use Dvelum\Response;

class Path extends Router
{
    /**
     * @var bool|Config\ConfigInterface
     */
    protected $appConfig = false;

    public function __construct()
    {
        $this->appConfig = Config::storage()->get('main.php');
    }

    /**
     * Route request
     * @param Request $request
     * @param Response $response
     * @throws \Exception
     * @return void
     */
    public function route(Request $request , Response $response) :void
    {
        $controller = $request->getPart(0);
        $controller = ucfirst(Filter::filterValue('pagecode' , $controller));

        $controllerClass = 'Frontend\\Index\\Controller';

        if($controller !== false && strlen($controller)){
            $classNamespace1 = 'Frontend_' . $controller . '_Controller';
            $classNamespace2 = 'Frontend\\' . $controller . '\\Controller';
            $classNamespace3 = 'Dvelum\\App\\Frontend\\' . $controller . '\\Controller';

            if(class_exists($classNamespace1)){
                $controllerClass = $classNamespace1;
            }elseif (class_exists($classNamespace2)){
                $controllerClass = $classNamespace2;
            }elseif (class_exists($classNamespace3)){
                $controllerClass = $classNamespace3;
            }
        }
        $this->runController($controllerClass , $request->getPart(1), $request, $response);
    }

    /**
     * Run controller
     * @param string $controller
     * @param null|string $action
     * @param Request $request
     * @param Response $response
     */
    public function runController(string $controller , ?string $action, Request $request , Response $response) : void
    {
        if((strpos('Backend_' , $controller) === 0) || strpos('\\Backend\\', $controller)!==false) {
            $response->redirect('/');
            return;
        }

        parent::runController($controller, $action, $request, $response);
    }


    /**
     * Define url address to call the module
     * The method locates the url of the published page with the attached
     * functionality
     * specified in the passed argument.
     * Thus, there is no need to know the exact page URL.
     *
     * @param string $module- module name
     * @return string
     */
    public function findUrl(string $module): string
    {
        return '/' . $module;
    }
}