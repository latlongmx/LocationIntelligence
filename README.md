# Proyecto WALMEX API Server



## Instalación y configuración
```
# curl -sS https://getcomposer.org/installer | php
# mv composer.phar /usr/local/bin/composer
# chmod +x /usr/local/bin/composer

$ composer global require "laravel/installer"
$ laravel new api
```


### deploying
```
composer update
php artisan vendor:publish
php artisan migrate

php artisan serve
```

### Generar API doc
```
php artisan l5-swagger:generate
```


## Estructura de archivos

```
|-- app
|   |-- Console
|   |   `-- Commands
|   |-- Events
|   |-- Exceptions
|   |-- Helpers             <-- Funciones globales
|   |-- Http
|   |   |-- Controllers
|   |   |   `-- Auth
|   |   |-- Middleware
|   |   |-- Requests
|   |   `-- Routes          <-- Rutas de la API
|   |       |-- GeoDynamic
|   |       `-- Workspace
|   |-- Jobs
|   |-- Listeners
|   |-- Models
|   |-- Policies
|   `-- Providers
|-- bootstrap
|-- config                  <-- Archivos de configuración
|-- database
|   |-- factories
|   |-- migrations
|   `-- seeds
|-- public
|-- resources
|-- storage
|   |-- api-docs
|   |-- app
|   |   `-- public
|   |-- framework
|   |   |-- cache
|   |   |-- sessions
|   |   `-- views
|   `-- logs
`-- tests
```



## Deploy to Server
PROD
curl -X POST -d "b=feature/api&f=api&w=prod" http://52.8.211.37/deploy/d.php

DEV
curl -X POST -d "b=feature/api&f=api&w=dev" http://52.8.211.37/deploy/d.php
