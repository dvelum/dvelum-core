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
use Dvelum\Request;
use Dvelum\Response;
use Dvelum\Config as Cfg;

class Config extends Router
{
    /**
     * @var Cfg\ConfigInterface
     */
    protected $appConfig;

    public function __construct()
    {
        $this->appConfig = Cfg::storage()->get('main.php');
    }

    /**
     * Route request
     * @param Request $request
     * @param Response $response
     */
    public function route(Request $request , Response $response) : void
    {
        $frontConfig = Cfg::storage()->get('frontend.php');
        $defaultController =  $frontConfig->get('default_controller');

        $controller = $request->getPart(0);
        $pathCode = \Dvelum\Filter::filterValue('pagecode' , $controller);
        $routes = Cfg::factory(Cfg\Factory::File_Array , $this->appConfig->get('frontend_modules'))->__toArray();

        if(isset($routes[$pathCode]) && class_exists($routes[$pathCode]['class']))
            $controllerClass = $routes[$pathCode]['class'];
        else
            $controllerClass = $defaultController;

        $this->runController($controllerClass , $request->getPart(1), $request, $response);
    }

    /**
     * @param string $controller
     * @param null|string $action
     * @param Request $request
     * @param Response $response
     */
    public function runController(string $controller , ?string $action, Request $request , Response $response) : void
    {
        if((strpos('Backend_' , $controller) === 0)){
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
     * @param string $module -module name
     * @return string
     */
    public function findUrl(string $module) : string
    {
        return '/'.$module;
    }
}