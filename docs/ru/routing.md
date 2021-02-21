Роутинг в DVelum
===

[<< документация](readme.md)

В дистрибутив DVelum включены три роутера для публичной части сайта
 (нужный вариант указан в extensions/dvelum-core/application/configs/dist/frontend.php), 
 при необходимости можно переопределить в application/configs/local/frontend.php: 
 
**возможные варианты**
'router' => 'path' // 'path', 'config'.

**path** - (Dvelum\App\Router\Path) Роутинг на основе файловых путей, 
например http://site.ru/news/list, в этом случае ищется App\Frontend\News\Controller::listAction, 
при отсутствии запускается Dvelum\App\Frontend\Index\Controller::indexAction. 

**config**  - (Dvelum\App\Router\Config) Роутинг на основе таблицы маршрутизации. 
Интерфейс управления модулями публичной части позволяет создавать алиасы (url-коды) запуска контроллеров,
 при отсутствии алиаса запускается Dvelum\App\Frontend\Index\Controller::indexAction.

--------------------
**console**  - Консольные приложения используют роутер Dvelum\App\Router\Console, он работает с настройками запуска [консольных Action](console.md)


