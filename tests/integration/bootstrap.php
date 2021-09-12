<?php

$skipBuild = false;
$dvelumRoot = str_replace('\\', '/', dirname(__FILE__, 3));
// should be without last slash
if ($dvelumRoot[strlen($dvelumRoot) - 1] == '/') {
    $dvelumRoot = substr($dvelumRoot, 0, -1);
}

define('DVELUM', true);
define('DVELUM_ROOT', $dvelumRoot);
define('DVELUM_WWW_PATH', $dvelumRoot . '/www/');
$_SERVER['DOCUMENT_ROOT'] = DVELUM_WWW_PATH;

chdir(DVELUM_ROOT);

//===== loading kernel =========
/*
 * Including initial config
 */
$bootCfg = include DVELUM_ROOT . '/application/configs/common/dist/init.php';
/*
 * Register composer autoload
 */
require DVELUM_ROOT . '/vendor/autoload.php';
/*
 * Including Autoloader class
 */
require DVELUM_ROOT . '/src/Dvelum/Autoload.php';
$autoloader = new \Dvelum\Autoload($bootCfg['autoloader']);

use Dvelum\Config\Factory as ConfigFactory;

$configStorage = ConfigFactory::storage();
$configStorage->setConfig($bootCfg['config_storage']);

//==== Loading system ===========
/*
 * Reload storage options from local system
 */
$configStorage->setConfig(ConfigFactory::storage()->get('config_storage.php')->__toArray());

/*
 * Connecting main configuration file
 */
$config = ConfigFactory::storage()->get('main.php');
$config->set('development', 2);
$configStorage->addPath('./application/configs/test/');

// orm extension testing
if (is_dir('./extensions/dvelum/dvelum-orm/configs/test')) {
    $configStorage->addPath('./extensions/dvelum/dvelum-orm/configs/test/');
}

/*
 * Setting autoloader config
 */
$autoloaderCfg = ConfigFactory::storage()->get('autoloader.php')->__toArray();
$autoloaderCfg['debug'] = $config->get('development');

if (!isset($autoloaderCfg['useMap'])) {
    $autoloaderCfg['useMap'] = true;
}

if ($autoloaderCfg['useMap'] && $autoloaderCfg['map']) {
    $autoloaderCfg['map'] = require ConfigFactory::storage()->getPath($autoloaderCfg['map']);
} else {
    $autoloaderCfg['map'] = false;
}

// Adding test directory for autoload
$autoloaderCfg['paths'][] = './tests/unit/application/classes';

$autoloader->setConfig($autoloaderCfg);

/*
 * Starting the application
 */
$appClass = $config->get('application');
if (!class_exists($appClass)) {
    throw new Exception('Application class ' . $appClass . ' does not exist! Check config "application" option!');
}

use Dvelum\Autoload;
use Dvelum\Config\Storage\StorageInterface;
use Dvelum\DependencyContainer;

$diContainer = new DependencyContainer();
$diContainer->bind('config.main', $config);
$diContainer->bind(StorageInterface::class, $configStorage);
$diContainer->bind(Autoload::class, $autoloader);
$diContainer->bindArray($configStorage->get('dependency.php')->__toArray());

$app = new $appClass($diContainer);
$app->runTestMode();

$locator = \Dvelum\Test\ServiceLocator::factory();
$locator->setContainer($diContainer);


$orm = $diContainer->get(\Dvelum\Orm\Orm::class);
$builderFactory = new \Dvelum\Orm\Record\BuilderFactory([]);
$lang = $diContainer->get(\Dvelum\Lang::class)->getDictionary();
if (!$skipBuild) {
    $dbObjectManager = new \Dvelum\Orm\Record\Manager($configStorage, $orm);
    foreach ($dbObjectManager->getRegisteredObjects() as $object) {
        echo 'build ' . $object . ' : ';
        $builder = $builderFactory->factory(
            $orm,
            $configStorage,
            $lang,
            $object
        );
        if ($builder->build(false)) {
            echo 'OK';
        } else {
            echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors()));
        }

        if (!$orm->config($object)->isDistributed()) {
            echo ' clear';
            $model = $orm->model($object);
            $db = $model->getDbConnection();
            $db->query('SET FOREIGN_KEY_CHECKS=0;');
            $db->delete($model->table());
            $db->query('SET FOREIGN_KEY_CHECKS=1;');
        }

        echo "\n";
    }
    echo PHP_EOL . 'BUILD FOREIGN KEYS' . PHP_EOL . PHP_EOL;
    foreach ($dbObjectManager->getRegisteredObjects() as $object) {
        echo 'build ' . $object . ' : ';
        $builder = $builderFactory->factory(
            $orm,
            $configStorage,
            $lang,
            $object
        );
        if ($builder->build(true)) {
            echo 'OK';
        } else {
            echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors()));
        }
        echo "\n";
    }
    echo 'BUILD SHARDS ' . PHP_EOL;

    $sharding = $configStorage->get('sharding.php');
    $shardsFile = $sharding->get('shards');
    $shardsConfig = $configStorage->get($shardsFile, true, false);
    $registeredObjects = $dbObjectManager->getRegisteredObjects();

    foreach ($shardsConfig as $item) {
        $shardId = $item['id'];
        echo "\t" . 'BUILD ' . $shardId . ' ' . PHP_EOL;

        foreach ($registeredObjects as $index => $object) {
            if (!$orm->config($object)->isDistributed()) {
                unset($registeredObjects[$index]);
                continue;
            }

            echo "\t\t" . $object . ' : ';

            $builder = $builderFactory->factory(
                $orm,
                $configStorage,
                $lang,
                $object
            );
            $builder->setConnection($orm->model($object)->getDbShardConnection($shardId));

            if ($builder->build(false, true)) {
                echo 'OK' . PHP_EOL;
            } else {
                $success = false;
                echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors())) . PHP_EOL;
            }

            $model = $orm->model($object);
            $db = $model->getDbShardConnection($shardId);
            $db->query('SET FOREIGN_KEY_CHECKS=0;');
            $db->delete($model->table());
            $db->query('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    foreach ($shardsConfig as $item) {
        $shardId = $item['id'];
        echo "\t" . 'BUILD KEYS ' . $shardId . ' ' . PHP_EOL;

        foreach ($registeredObjects as $index => $object) {
            echo "\t\t" . $object . ' : ';

            $builder = $builderFactory->factory(
                $orm,
                $configStorage,
                $lang,
                $object
            );
            $builder->setConnection($orm->model($object)->getDbShardConnection($shardId));

            if ($builder->build(true, true)) {
                echo 'OK' . PHP_EOL;
            } else {
                $success = false;
                echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors())) . PHP_EOL;
            };
        }
    }
}

echo '==================' . PHP_EOL;

// init default objects
/*
$session = \Dvelum\App\Session\User::factory();
$session->setId(1);
$session->setAuthorized();
*/

$group = $orm->record('Group');
$group->setInsertId(1);
$group->setValues(
    ['title' => date('YmdHis'), 'system' => false]
);
$group->save();

$user = $orm->record('User');
$user->setInsertId(1);
$user->setValues(
    [
        'login' => uniqid(date('YmdHis'), true),
        'pass' => '111',
        'email' => uniqid(date('YmdHis'), true) . '@mail.com',
        'enabled' => 1,
        'admin' => 1,
        'name' => 'Test User',
        'group_id' => $group->getId()
    ]
);
$user->save();
