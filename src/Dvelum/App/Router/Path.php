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

use Dvelum\App\Frontend\Index;
use Dvelum\App\Router;
use Dvelum\Config;
use Dvelum\Filter;
use Dvelum\Request;
use Dvelum\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Path extends Router
{

    /**
     * Run action
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws \Exception
     */
    public function route(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface{
        $controller = $request->getPart(0);
        $controller = ucfirst(Filter::filterValue('pagecode' , $controller));

        $controllerClass = Index\Controller::class;

        if($controller !== false && strlen($controller)){
            $classNamespace1 = 'App\\Frontend\\' . $controller . '\\Controller';
            $classNamespace2 = 'Dvelum\\App\\Frontend\\' . $controller . '\\Controller';

            if(class_exists($classNamespace1)){
                $controllerClass = $classNamespace1;
            }elseif (class_exists($classNamespace2)){
                $controllerClass = $classNamespace2;
            }
        }
        $requestHelper = new \Dvelum\Request($request);
        $responseHelper = new \Dvelum\Response($response);
        $this->runController($controllerClass , $request->getPart(1), $requestHelper, $responseHelper);
        $responseHelper->send();
        return $responseHelper->getPsrResponse();
    }

    /**
     * Run controller
     * @param string $controller
     * @param null|string $action
     * @param Request $request
     * @param Response $response
     */
    public function runController(string $controller , ?string $action, Request $request , Response $response) : ResponseInterface
    {
        if(strpos('\\Backend\\', $controller)!==false) {
            $response->redirect('/');
            return $response->getPsrResponse();
        }

        return parent::runController($controller, $action, $request, $response);
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
