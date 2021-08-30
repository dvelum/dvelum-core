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

namespace Dvelum\App\Controller;

class EventManager
{
    /**
     * @var array
     */
    protected $listeners = [];
    /**
     * @var string
     */
    protected $error = '';

    public const BEFORE_LIST = 'before_list';
    public const AFTER_LIST = 'after_list';
    public const BEFORE_LOAD = 'before_load';
    public const BEFORE_LINKED_LIST = 'before_linked_list';
    public const AFTER_LOAD = 'after_load';
    public const AFTER_LINKED_LIST = 'after_linked_list';

    public const AFTER_UPDATE_BEFORE_COMMIT = 'after_update_before_commit';
    public const AFTER_INSERT_BEFORE_COMMIT = 'after_insert_before_commit';

    /**
     * @param string $event
     * @param callable|array $handler [obj,method]
     * @return void
     */
    public function on(string $event, $handler): void
    {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $listener = new \stdClass();
        $listener->handler = $handler;

        $this->listeners[$event][] = $listener;
    }

    /**
     * @param mixed $event
     * @param \stdClass $data
     * @return bool
     */
    public function fireEvent($event, \stdClass $data): bool
    {
        $this->error = '';

        if (!isset($this->listeners[$event])) {
            return true;
        }

        $e = new Event();
        $e->setData($data);

        foreach ($this->listeners[$event] as $listener) {
            if ($e->isPropagationStopped()) {
                return false;
            }

            if (is_callable($listener->handler)) {
                ($listener->handler)($e);
            } else {
                call_user_func_array($listener->handler, [$e]);
            }

            if ($e->hasError()) {
                $this->error = $e->getError();
                return false;
            }
        }
        return true;
    }

    /**
     * Get event error message
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }
}
