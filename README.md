# SuperApp SDK

## Composer account

The composer account used was carlos.miranda@undostres.com.mx, for troubleshooting please contact.

---

## Composer installation

Composer it's needed, if missing do:

```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"
```

---

## Installation

If composer.json is present, and has de package in the requeriment list do:

```
composer install
``` 

Otherwise, use the requiere instruction to add or create a composer.json

```
composer require undostres-com-mx/superapp_sdk
```

---

## Update

To update this package, inside the composer.json folder do:

```
composer update
``` 

---

## Usage

The use of composer requies the usage of `require_once('/vendor/autoload.php');`

Import the SDK use `use UDT\SDK;`;

Instance the class `$sdk = SDK(hashKey, server);`

There you can do:

- encryptSDK
    - Encrypt string with 3des algorithm.
- decryptSDK
    - Decrypt string with 3des algorithm.
- decryptUDT
    - Decrypt string with UDT algorithm.
- validateRequestHeaders
    - Check api/token to see if request is authentic.
- createPayment
    - Create an order and retrieve payment url.
- cancelOrder
    - Cancel a pending order on UDT..
- refundOrder
    - Refund a payed order on UDT.

Do the following on project root folder to use the key utilities:

```
php .\src\KeyUtils.php
``` 

---

## Authors

- Carlos Miranda
- Adrian García
