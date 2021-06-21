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

namespace Dvelum\App;

use Dvelum\Lang;
use Dvelum\Request;
use Dvelum\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Base class for routing of requests
 */
abstract class Router implements Router\RouterInterface
{

    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Run action
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return void
     * @throws \Exception
     */
    abstract public function route(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;

    /**
     * Calc url for module
     * @param string $module — module name
     * @return string
     */
    abstract public function findUrl(string $module): string;

    /**
     * Run controller
     * @param string $controller
     * @param null|string $action
     * @param Request $request
     * @param Response $response
     * @throws \Exception
     */
    public function runController(string $controller, ?string $action, Request $request, Response $response): void
    {
        if (!class_exists($controller)) {
            throw new \Exception('Undefined Controller: ' . $controller);
        }

        /**
         * @var \Dvelum\App\Controller $controller
         */
        $controller = new $controller($request, $response, $this->container);
        $controller->setRouter($this);

        if ($response->isSent()) {
            return;
        }

        if ($controller instanceof Router\RouterInterface) {
            $controller->route($request, $response);
        } else {
            if (empty($action)) {
                $action = 'index';
            }

            if (!method_exists($controller, $action . 'Action')) {
                $action = 'index';
                if (!method_exists($controller, $action . 'Action')) {
                    $response->error(Lang::lang()->get('WRONG_REQUEST') . ' ' . $request->getUri());
                    return;
                }
            }
            $controller->{$action . 'Action'}();
        }

        if (!$response->isSent() && method_exists($controller, 'showPage')) {
            $controller->showPage();
        }
    }
}