# TXTCONNECT PHP SDK

## WORK IN PROGRESS

> txt connect SMS API provides convenient way to send single SMS, check balance status,delivery status and also fetch all SMS inbox.
>
> To start integrating with txt connect, you need to create an [account](https://txtconnect.net/signup). If an account is created and successfully logged in,you then have to [request for sender_id](https://txtconnect.net/customers/sender-ids-management) and [generate SMS API](https://txtconnect.net/customers/campaigns-api/sms/user-sms-api-info) key which can be Updated.

## Installation

```shell
composer require prinx/txtconnect
```

## Usage

### Basic

```php
// require 'path/to/vendor/autoload.php'; // Not needed if using the package inside a framework.

use Prinx\Txtconnect\Sms;

$message = 'Hello World';
$number = '233...'; // See number formats below

$sms = new Sms;

$response = $sms->send($message, $number);
```

### Specifying the HTTP method

```php
use Prinx\Txtconnect\Sms;

$method = 'POST'; // GET|POST

$sms = new Sms;

$response = $sms->send($message, $phone, $method);
```

### Number formats

The number is automatically sanitized, any space is removed and is internationalized. This allows to pass the number without worrying about the correct format.

For example, the number +233242424242 can be passed as:

- 024 24 24 242
- 024 24 24      242 // Any space will be removed. You don't need to care about it
- 233(0)24 24 24 242
- 0 24 24-24-242
- 024 2424 242
- etc

The number can be sent in international format: starting with the '+' sign (OPTIONAL), then the country code then the number itself.

Eg: +233 11 11 11 111

In that case, the package will automatically determine the country of the number.

If you don't have the number in international format, you can still send the SMS, just by setting the country yourself before calling the `send` method:

```php

$sms = new Sms;

$response = $sms->country('GH')->send($message, $phone);
```

### Invalid numbers

## License

[MIT](LICENSE)
