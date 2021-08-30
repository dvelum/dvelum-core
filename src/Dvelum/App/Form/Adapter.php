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

namespace Dvelum\App\Form;

use Dvelum\Config\ConfigInterface;
use Dvelum\Request;
use Dvelum\Lang;
use Dvelum\App\Form;

abstract class Adapter
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var ConfigInterface $config
     */
    protected $config;
    /**
     * @var Lang\Dictionary
     */
    protected $lang;

    /**
     * Adapter constructor.
     * @param Request $request
     * @param Lang\Dictionary $lang
     * @param ConfigInterface $config
     */
    public function __construct(Request $request, Lang\Dictionary $lang, ConfigInterface $config)
    {
        $this->lang = $lang;
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * @return bool
     */
    abstract public function validateRequest(): bool;

    /**
     * @return mixed
     */
    abstract public function getData();

    /**
     * Get list of errors
     * @return Form\Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
