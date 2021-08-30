[![PHP Version](https://img.shields.io/badge/php-7.4%2B-blue.svg)](https://packagist.org/packages/dvelum/dvelum-core)
[![Total Downloads](https://img.shields.io/packagist/dt/dvelum/dvelum-core.svg?style=flat-square)](https://packagist.org/packages/dvelum/dvelum-core)
![Build and Test](https://github.com/dvelum/dvelum-core/workflows/Build%20and%20Test/badge.svg?branch=4.x-dev&event=push)

DVelum 4.x Core
======

New version of DVelum core. Better performance.

Highest level of PHPStan static analysis.

Dependency injections using constructors and DependencyContainer, Lazy initialization.

ORM and ORM-UI support.

[Docs](./docs/ru/readme.md)

Local installation
-----

```
composer create-project dvelum/dvelum-core
```

Apache VirtualHost configuration example
```
<VirtualHost *:80>
    ServerName dvelum.local
    DocumentRoot /path/to/dvelum/www
    <Directory "/path/to/dvelum/www">
        Require all granted
        AllowOverride All
        Options +ExecCGI -Includes -Indexes
     </Directory>
</VirtualHost>
```
Add the local domain to /etc/hosts file
```
127.0.0.1 dvelum.local
```

### Prepare for production
```
// remove development extensions
composer install --no dev

// generate class map for better performance
php ./console.php /generateClassMap
```


