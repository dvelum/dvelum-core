<?php

/*
 *
 * DVelum project https://github.com/dvelum/
 *
 * MIT License
 *
 *  Copyright (C) 2011-2021  Kirill Yegorov https://github.com/dvelum/dvelum-core
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *  SOFTWARE.
 *
 */

namespace Dvelum\App\Controller;

class Event
{
    /**
     * @var string
     */
    public $type;
    /**
     * @var bool
     */
    protected $stop = false;
    /**
     * @var mixed
     */
    protected $data;
    /**
     * @var bool
     */
    protected $error = false;
    /**
     * @var string
     */
    protected $errorMessage = '';

    /**
     * Stop event propagation
     * @return void
     */
    public function stopPropagation(): void
    {
        $this->stop = true;
    }

    /**
     * Is event propagation stopped
     * @return bool
     */
    public function isPropagationStopped(): bool
    {
        return $this->stop;
    }

    /**
     * Set error message
     * @param string $message
     */
    public function setError(string $message): void
    {
        $this->stopPropagation();
        $this->error = true;
        $this->errorMessage = $message;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->errorMessage;
    }

    /**
     * Set event data
     * @param \stdClass $data
     */
    public function setData(\stdClass $data): void
    {
        $this->data = $data;
    }

    /**
     * Get event data
     * @return \stdClass
     */
    public function getData(): \stdClass
    {
        return $this->data;
    }
}
