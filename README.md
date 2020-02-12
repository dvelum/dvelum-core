[![PHP Version](https://img.shields.io/badge/php-7.3%2B-blue.svg)](https://packagist.org/packages/dvelum/dvelum-core)
[![Total Downloads](https://img.shields.io/packagist/dt/dvelum/dvelum-core.svg?style=flat-square)](https://packagist.org/packages/dvelum/dvelum-core)
[![Build Status](https://travis-ci.org/dvelum/dvelum-core.svg?branch=master)](https://travis-ci.org/dvelum/dvelum-core)


[Внимание, стабильная ветка 2.x](https://github.com/dvelum/dvelum/tree/2.x)
===

DVelum 3.x Core (Experimental repo)
======


Local installation
-----

```
composer create-project dvelum/dvelum
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
Add local domain to /etc/hosts
```
127.0.0.1 dvelum.local
```

Open Web Browser at http://dvelum.local/

Issues https://github.com/dvelum/dvelum/issues



