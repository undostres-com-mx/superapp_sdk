# SuperApp SDK

## Composer account

The composer account used was carlos.miranda@undostres.com.mx, for troubleshooting please contact.

---

## Composer installation

Composer is needed, if missing do:

```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"
```

---

## Installation

If composer.json is present, and has the package in the requirement list do:

```
composer install
``` 

Otherwise, use the require instruction to add or create a composer.json

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

The use of composer requires the usage of `require_once('/vendor/autoload.php');`

Import the SDK using `use UDT\SDK\SASDK;`

Initialize the static class `SASDK::init(hashKey, server);`

There you can do:

- encryptSDK
  - Encrypt string with 3des algorithm.
- decryptSDK
  - Decrypt string with 3des algorithm.
- decryptUDT
  - Decrypt string with UDT algorithm.
- validateRequestHeaders
  - Check api/token to see if request is authentic.
- formatMoney
  - Gives the standard UDT format to money, decimal separated by dot, no comma.
- createPayment
  - Create an order and retrieve payment url.
- cancelOrder
  - Cancel a pending order on UDT.
- refundOrder
  - Refund a paid order on UDT.

Do the following on project root folder to use the key utilities:

```
php .\Utils\KeyUtils.php
``` 

---

## Testing

To make test calls it's needed to have a ssl certificate, to do so download the certificate and update your **php.ini** with yout path like this:

```
[curl]
curl.cainfo="C:/php/ssl/cacert.pem"
openssl.cafile="C:/php/ssl/cacert.pem"
``` 

The certificate can be downloaded [here.](http://curl.haxx.se/ca/cacert.pem)

Do the following on project root folder to open the testing utilities:

```
php .\Utils\Test.php
``` 

Then you can make successful calls.

Keep in mind that to make a payment you need a **return** and **callback** api. If you don't add it to the request the payment is always going to fail.

---

## Authors

- Carlos Miranda
- Adrian Garcia
