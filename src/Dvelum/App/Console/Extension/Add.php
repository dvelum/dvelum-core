<?php
/**
 *  DVelum project https://github.com/dvelum/dvelum , https://github.com/k-samuel/dvelum , http://dvelum.net
 *  Copyright (C) 2011-2019  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
        $request = Request::factory();
        $vendor = Filter::filterString((string)$request->getPart(1));
        $extension = Filter::filterString((string)$request->getPart(2));

        if(empty($vendor) || empty($extension)){
            return false;
        }

        $moduleDir = $this->appConfig->get('extensions')['path'] . '/' . $vendor . '/' . $extension;

        if(!is_dir($moduleDir)){
            return false;
        }

        if(!file_exists($moduleDir.'/extension_config.php')){
            return false;
        }

        $moduleInfo = include $moduleDir . '/extension_config.php';
        if(!is_array($moduleInfo) || empty($moduleInfo)){
            return false;
        }

        $moduleId = $vendor . '/' . $extension;
        /**
         * @var Autoload $autoload
         */
        $autoload = Service::get('autoloader');

        $manager = new Manager($this->appConfig , $autoload);

        if($manager->extensionRegistered($moduleId)){
            return true;
        }

        $config = array_merge([
            'enabled' => true,
            'dir' => $moduleId
        ], $moduleInfo);

        return $manager->add($moduleId, $config);
    }
}