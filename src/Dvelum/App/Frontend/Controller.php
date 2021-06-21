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

namespace Dvelum\App\Frontend;

use Dvelum\{App, Config, Config\ConfigInterface, Lang, Page\Page, Request, Response, Resource};
use Psr\Container\ContainerInterface;

class Controller extends App\Controller
{
    /**
     * @var ConfigInterface
     */
    protected ConfigInterface $frontendConfig;
    /**
     * @var Lang\Dictionary
     */
    protected Lang\Dictionary $lang;
    /**
     * @var Page
     */
    protected Page $page;

    protected ContainerInterface $container;

    public function __construct(Request $request, Response $response, ContainerInterface $container)
    {
        $this->page = Page::factory();
        $this->frontendConfig = Config::storage()->get('frontend.php');
        $this->lang = $container->get(\Dvelum\Lang::class)::lang();
        parent::__construct($request, $response);
    }

    /**
     * Show Page.
     * Running this method initiates rendering of templates and sending of HTML data.
     * @return void
     */
    public function showPage(): void
    {
        header('Content-Type: text/html; charset=utf-8');
        $this->page->setTemplatesPath('public/');

        $layoutPath = $this->page->getThemePath() . 'layout.php';
        $this->render($layoutPath, [
            'development' => $this->appConfig->get('development'),
            'page' => $this->page,
            'path' => $this->page->getThemePath(),
            'resource' => Resource::factory()
        ], false);
    }
}