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

declare(strict_types=1);

namespace Dvelum\App\Console\Extension;

use Dvelum\App\Console;
use Dvelum\Autoload;
use Dvelum\Extensions\Manager;
use Dvelum\Filter;
use Dvelum\Request;
use Dvelum\Service;

class Add extends Console\Action
{
    public function action(): bool
    {
        $params = $this->params;

        $vendor = Filter::filterString((string)$params[0]);
        $extension = Filter::filterString((string)$params[1]);

        if (empty($vendor) || empty($extension)) {
            return false;
        }

        $moduleDir = $this->appConfig->get('extensions')['path'] . $vendor . '/' . $extension;

        if (!is_dir($moduleDir)) {
            return false;
        }

        if (!file_exists($moduleDir . '/extension_config.php')) {
            return false;
        }

        $moduleInfo = include $moduleDir . '/extension_config.php';
        if (!is_array($moduleInfo) || empty($moduleInfo)) {
            return false;
        }

        $moduleId = $vendor . '/' . $extension;
        /**
         * @var \Dvelum\Extensions\Manager $manager
         */
        $manager = $this->diContainer->get(\Dvelum\Extensions\Manager::class);

        if ($manager->extensionRegistered($moduleId)) {
            return true;
        }

        $config = array_merge([
                                  'enabled' => true,
                                  'dir' => $moduleId
                              ], $moduleInfo);

        return $manager->add($moduleId, $config);
    }
}
