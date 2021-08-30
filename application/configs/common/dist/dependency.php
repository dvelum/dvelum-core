<?php

use Psr\Container\ContainerInterface as c;
use Dvelum\DependencyContainer\Argument;
use Dvelum\DependencyContainer\CallableArgument;

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
    \Dvelum\Template\Storage::class => [
        'class' => \Dvelum\Template\Storage::class,
        'arguments' => [
            new Argument('config.template_storage'),
        ]
    ],

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
            ) : null
        ];
    },
    \Dvelum\Resource::class => [
        'class' => \Dvelum\Resource::class,
        'arguments' => [
            new Argument('config.resource')
        ]
    ],
    \Dvelum\Db\ManagerInterface::class => static function (c $c) {
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
    \Dvelum\Extensions\Manager::class => [
        'class' => \Dvelum\Extensions\Manager::class,
        'arguments' => [
            new Argument('config.main'),
            new CallableArgument(static function(c $c){return $c;})
        ]
    ],
    'config.lang_storage' => static function (c $c) {
        return $c->get(\Dvelum\Config\Storage\StorageInterface::class)->get('lang_storage.php')->__toArray();
    },
    'LangStorage' => [
        'class' => \Dvelum\Config\Storage\File\AsArray::class,
        'arguments' => [
            new Argument('config.lang_storage')
        ]
    ],
    // === services =======================================================================
    \Dvelum\Lang::class => [
        'class' => \Dvelum\Lang::class,
        'arguments' => [
            new Argument('LangStorage'),
            new CallableArgument(static function(c $c){
                return $c->get('config.main')->get('language');
            }),
            new CallableArgument(static function(c $c){
                $lang = $c->get('language');
                return [
                    [
                        'name' => $lang,
                        'src' => $lang . '.php',
                        'type' => 1 // \Dvelum\Config\Factory::File_Array
                    ]
                ];
            }),
        ]
    ],
    \Dvelum\App\Dictionary\Service::class => [
        'class' => \Dvelum\App\Dictionary\Service::class,
        'arguments' => [
            new CallableArgument(
                static function (c $c) {
                    $config = $c->get('config.main');
                    return call_user_func(
                        [\Dvelum\Config\Factory::class, 'create'],
                        [
                            [
                                'configPath' => $config->get('dictionary_folder') . $config->get('language') . '/'
                            ]
                        ]
                    );
                }
            )
        ]
    ],
    \Dvelum\Template\Service::class => [
        'class' => \Dvelum\Template\Service::class,
        'arguments' => [
            new CallableArgument(static function (c $c) {
                return $c->get(\Dvelum\Config\Storage\StorageInterface::class)->get('template.php');
            }),
            new Argument(\Dvelum\Template\Storage::class),
            new Argument(\Dvelum\Cache\CacheInterface::class)
        ]
    ],

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
    }
];
