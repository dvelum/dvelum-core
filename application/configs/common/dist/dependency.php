<?php

use Psr\Container\ContainerInterface as c;

return [
    \Dvelum\App\Cache\Manager::class => \Dvelum\App\Cache\Manager::class,
    \Dvelum\Cache\CacheInterface::class => static function (c $c): ?\Dvelum\Cache\CacheInterface {
        if (!$c->get('config.main')->get('use_cache')) {
            return null;
        }
        return $c->get(\Dvelum\App\Cache\Manager::class)->connect(
            'default',
            $c->get(\Dvelum\Config\Storage\StorageInterface::class)->get('cache.php')->__toArray()
        );
    },
    \Dvelum\Template\Storage::class => static function (c $c): \Dvelum\Template\Storage {
        return new \Dvelum\Template\Storage(
            $c->get(\Dvelum\Config\Storage\StorageInterface::class)->get('template_storage.php')->__toArray()
        );
    },
    \Dvelum\Resource::class => static function (c $c): \Dvelum\Resource {
        $resource = \Dvelum\Resource::factory();
        $config = $c->get('config.main');
        $resource->setConfig(
            \Dvelum\Config\Factory::create(
                [
                    'jsCacheUrl' => $config['jsCacheUrl'],
                    'jsCachePath' => $config['jsCachePath'],
                    'cssCacheUrl' => $config['cssCacheUrl'],
                    'cssCachePath' => $config['cssCachePath'],
                    'wwwRoot' => $config['wwwRoot'],
                    'wwwPath' => $config['wwwPath'],
                    'cache' => $c->has(\Dvelum\Cache\CacheInterface::class) ? $c->get(
                        \Dvelum\Cache\CacheInterface::class
                    ) : null
                ]
            )
        );
        return $resource;
    },
    \Dvelum\Db\ManagerInterface::class => static function (c $c): \Dvelum\Db\ManagerInterface {
        $config = $c->get('config.main');
        $useProfiler = false;
        if ($config->get('development') && $config->get('debug_panel')) {
            $useProfiler = $c->get(\Dvelum\Config\Storage\StorageInterface::class)->get('debug_panel.php')->get(
                'options'
            )['sql'];
        }
        $config->set('use_db_profiler', $useProfiler);
        $managerClass = $config->get('db_manager');
        return new $managerClass($config);
    },
    \Dvelum\Extensions\Manager::class => static function (c $c): \Dvelum\Extensions\Manager {
        return new \Dvelum\Extensions\Manager($c->get('config.main'), $c);
    },
    // === services =======================================================================
    \Dvelum\Lang::class => static function (c $c): \Dvelum\Lang {
        $language = $c->get('config.main')->get('language');
        $langService = new Dvelum\Lang();
        $langService->addLoader(
            $language,
            $language . '.php',
            \Dvelum\Config\Factory::File_Array
        );
        $langService->setDefaultDictionary($language);
        $langStorage = $langService->getStorage();
        $langStorage->setConfig(
            $c->get(\Dvelum\Config\Storage\StorageInterface::class)->get('lang_storage.php')->__toArray()
        );
        return $langService;
    },
    \Dvelum\App\Dictionary\Service::class => static function (c $c): \Dvelum\App\Dictionary\Service {
        $config = $c->get('config.main');
        $service = new \Dvelum\App\Dictionary\Service();
        $service->setConfig(
            Dvelum\Config\Factory::create(
                [
                    'configPath' => $config->get('dictionary_folder') . $config->get('language') . '/'
                ]
            )
        );
        return $service;
    },
    \Dvelum\Template\Storage::class => static function (c $c): \Dvelum\Template\Storage {
        return new \Dvelum\Template\Storage(
            $c->get(\Dvelum\Config\Storage\StorageInterface::class)->get('template_storage.php')->__toArray()
        );
    },
    \Dvelum\Template\Service::class => static function (c $c): \Dvelum\Template\Service {
        $configStorage = $c->get(\Dvelum\Config\Storage\StorageInterface::class);
        return new \Dvelum\Template\Service(
            $configStorage->get('template.php'),
            $c->get(\Dvelum\Template\Storage::class),
            $c->has(\Dvelum\Cache\CacheInterface::class) ? $c->get(
                \Dvelum\Cache\CacheInterface::class
            ) : null
        );
    },

    \Laminas\Mail\Transport\TransportInterface::class => static function (c $c
    ): \Laminas\Mail\Transport\TransportInterface {
        $cfg = $c->get(\Dvelum\Config\Storage\StorageInterface::class)->get('mail_transport.php')->__toArray();
        /**
         * @var \Laminas\Mail\Transport\TransportInterface $transport
         */
        $transport = new $cfg['adapter'];
        if (!empty($cfg['config']['optionsAdapter']) && !empty($cfg['config']['options']) && method_exists(
                $transport,
                'setOptions'
            )) {
            $transport->setOptions(new $cfg['config']['optionsAdapter']($cfg['config']['options']));
        }
        return $transport;
    }
];
