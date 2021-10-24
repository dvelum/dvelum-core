<?php

use Psr\Container\ContainerInterface as c;

return [
    \Dvelum\App\Cache\Manager::class => \Dvelum\App\Cache\Manager::class,
    \Dvelum\Cache\CacheInterface::class => static function (c $c) {
        if (!$c->get('config.main')->get('use_cache')) {
            return null;
        }
        return $c->get(\Dvelum\App\Cache\Manager::class)->connect(
            'default',
            $c->get(\Dvelum\Config\Storage\StorageInterface::class)->get('cache.php')->__toArray()
        );
    },
    'config.template_storage' => static function (c $c) {
        return $c->get(\Dvelum\Config\Storage\StorageInterface::class)->get('template_storage.php');
    },
    \Dvelum\Template\Storage::class => static function (c $c) {
        return new \Dvelum\Template\Storage($c->get('config.template_storage'));
    },
    'config.resource' => static function (c $c) {
        $config = $c->get('config.main');
        return [
            'jsCacheUrl' => $config['jsCacheUrl'],
            'jsCachePath' => $config['jsCachePath'],
            'cssCacheUrl' => $config['cssCacheUrl'],
            'cssCachePath' => $config['cssCachePath'],
            'wwwRoot' => $config['wwwRoot'],
            'wwwPath' => $config['wwwPath'],
            'cache' => $c->has(\Dvelum\Cache\CacheInterface::class) ? $c->get(
                \Dvelum\Cache\CacheInterface::class
            ) : null,
        ];
    },
    \Dvelum\Resource::class => static function (c $c) {
        return new  \Dvelum\Resource($c->get('config.resource'));
    },
    \Dvelum\Db\ManagerInterface::class => static function (c $c) {
        $config = $config = $c->get('config.main');
        $useProfiler = false;
        if ($config->get('development') && $config->get('debug_panel')) {
            $useProfiler = $c->get(\Dvelum\Config\Storage\StorageInterface::class)->get('debug_panel.php')->get(
                'options'
            )['sql'];
        }
        $config->set('use_db_profiler', $useProfiler);
        return new \Dvelum\Db\Manager($config);
    },
    \Dvelum\Extensions\Manager::class => static function (c $c) {
        return new \Dvelum\Extensions\Manager($c->get('config.main'), $c);
    },
    'config.lang_storage' => static function (c $c) {
        return $c->get(\Dvelum\Config\Storage\StorageInterface::class)->get('lang_storage.php')->__toArray();
    },
    'LangStorage' => static function (c $c) {
        return new \Dvelum\Config\Storage\File\AsArray($c->get('config.lang_storage'));
    },
    // === services =======================================================================
    \Dvelum\Lang::class => static function (c $c) {
        $lang = $c->get('config.main')->get('language');
        return new \Dvelum\Lang(
            $c->get('LangStorage'),
            $lang,
            [
                [
                    'name' => $lang,
                    'src' => $lang . '.php',
                    'type' => \Dvelum\Config\Factory::FILE_ARRAY,
                ],
            ]
        );
    },
    \Dvelum\App\Dictionary\Service::class => static function (c $c) {
        $config = $c->get('config.main');
        $config = \Dvelum\Config\Factory::create(
            [
                [
                    'configPath' => $config->get('dictionary_folder') . $config->get('language') . '/',
                ],
            ]
        );
        return new \Dvelum\App\Dictionary\Service($config);
    },
    \Dvelum\Template\Service::class => static function (c $c) {
        return new \Dvelum\Template\Service(
            $c->get(\Dvelum\Config\Storage\StorageInterface::class)->get('template.php'),
            $c->get(\Dvelum\Template\Storage::class),
            $c->get(\Dvelum\Cache\CacheInterface::class)
        );
    },
    \Laminas\Mail\Transport\TransportInterface::class => static function (c $c) {
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
    },
    // ==================
    Dvelum\App\Dictionary\Manager::class => static function (c $c) {
        return new  Dvelum\App\Dictionary\Manager(
            $c->get(\Dvelum\Lang::class),
            $c->get('config.main'),
            $c->get(\Dvelum\Config\Storage\StorageInterface::class),
            $c->get(\Dvelum\App\Dictionary\Service::class),
            $c->get(\Dvelum\Cache\CacheInterface::class)
        );
    },
];
