# SuperApp SDK

## Composer

The composer account used was carlos.miranda@undostres.com.mx, composer do updates on push throught GitHub hook, but can be manually updated on the packages list webpage.

## Composer installation

Composer it's needed, if missing do:

```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"
```

## Installation

If composer.json is present, and has de package in the requeriment list do:

```
composer install
``` 

Otherwise, use the requiere instruction to add or create a composer.json

```
composer require undostres-com-mx/gratissdk
``` 

## Update

To update this package, inside the composer.json folder do:

```
composer update
``` 

## Authors

- Adrian García
- Manuel Carretero
- Carlos Miranda
