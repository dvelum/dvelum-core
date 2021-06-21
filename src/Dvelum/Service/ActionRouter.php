<?php
/**
 * DVelum project https://github.com/dvelum/dvelum-core , https://github.com/dvelum/dvelum
 *
 * MIT License
 *
 * Copyright (C) 2011-2021  Kirill Yegorov
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

namespace App\Service;


use App\Config\Config;
use Psr\Http\Message\ServerRequestInterface;

class ActionRouter
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param ServerRequestInterface $req
     * @return ActionInterface
     * @throws \Exception
     */
    public function getAction(ServerRequestInterface $req): ActionInterface
    {
        $routeStartIndex = $this->config->get('uri_path_start_index');
        $routes = $this->config->get('routes');

        $uri = $req->getUri()->getPath();
        $parts = explode('/', trim($uri,'/'));
        $routeParts = [];
        foreach ($parts as $index => $value) {
            if ($index >= $routeStartIndex) {
                $routeParts[] = $value;
            }
        }
        $action = '';
        if (!empty($routeParts)) {
            $action = implode('/', $routeParts);
        }

        if (array_key_exists($action, $routes)) {
            $routeConfig = $routes[$action];
        } else {
            if(!isset($routes[$this->config->get('default_route')])){
                throw new \RuntimeException('undefined default action route '.$this->config->get('default_route'));
            }
            $routeConfig = $routes[$this->config->get('default_route')];
        }

        $action = new $routeConfig['class'];
        if (!$action instanceof ActionInterface) {
            throw new \RuntimeException($routeConfig['class'] . ' should implement ' . ActionInterface::class);
        }
        return new $routeConfig['class'];
    }
}