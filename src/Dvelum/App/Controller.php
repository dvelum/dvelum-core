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

use Dvelum\Config;
use Dvelum\Request;
use Dvelum\Response\ResponseInterface;
use Dvelum\App\Router\RouterInterface;
use Psr\Container\ContainerInterface;

class Controller
{
    /**
     * @var Request
     */
    protected Request $request;
    /**
     * @var ResponseInterface
     */
    protected ResponseInterface $response;
    /**
     * @var Config\ConfigInterface<string,mixed>
     */
    protected Config\ConfigInterface $appConfig;
    /**
     * @var RouterInterface
     */
    protected RouterInterface $router;

    protected ContainerInterface $container;

    /**
     * Controller constructor.
     * @param Request $request
     * @param ResponseInterface $response
     */
    public function __construct(Request $request, ResponseInterface $response, ContainerInterface $container)
    {
        $this->container = $container;
        $this->request = $request;
        $this->response = $response;
        $this->appConfig = $container->get('config.main');
    }

    /**
     * Set link to router
     * @param Router\RouterInterface $router
     * @return void
     */
    public function setRouter(Router\RouterInterface $router): void
    {
        $this->router = $router;
    }

    /**
     * Render template
     * @param string $templatePath
     * @param array<string,mixed> $data
     * @param bool $cacheResult
     */
    public function render(string $templatePath, array $data, bool $cacheResult = true): void
    {
        $template = $this->container->get(\Dvelum\Template\Service::class)->getTemplate();
        if (!$cacheResult) {
            $template->disableCache();
        }
        $template->setData($data);
        $this->response->put($template->render($templatePath));
    }
}
