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

### Configuration

Create a `.env` file at the root of your project (where the vendor folder is located), if none exists already.

In the `.env` file specify your TxtConnect API credentials:

```ini
TXTCONNECT_KEY="api_key"
TXTCONNECT_SENDER_ID="sender_id"
```

### Sending SMS

```php
require 'path/to/vendor/autoload.php'; // Not needed if using the package inside a framework.

use Prinx\Txtconnect\Sms;

$message = 'Hello World';
$number = '233...'; // See number formats below

$sms = new Sms;

$response = $sms->send($message, $number);
```

#### Specifying the HTTP method

```php
use Prinx\Txtconnect\Sms;

$method = 'POST'; // GET|POST

$sms = new Sms;

$response = $sms->send($message, $phone, $method);
```

#### Number formats

The number is automatically sanitized, any space is removed and is internationalized. This allows to pass the number without worrying about the correct format.

For example, the number +233242424242 can be passed as:

- 024 24 24 242
- 233(0)24 24 24 242
- 0 24 24-24-242
- 024 2424 242
- etc

The number can be sent in international format: starting with the '+' sign (OPTIONAL), then the country code then the number itself.

Eg: +233 11 11 11 111

In that case, the package will automatically determine the country of the number.

#### Specifying the default country

Specifying the default country allows you to send SMS using numbers in the national format of that country. For example, after specifying the default country as Ghana, you can send SMS to numbers like 024 24 24 242, without putting them in international format.

```php
$sms = new Sms;

$response = $sms->country('GH')->send($message, $phone);
```

### Get SMS status

```php
require 'path/to/vendor/autoload.php'; // Not needed if using the package inside a framework.

use Prinx\Txtconnect\SmsStatus;

$status = new SmsStatus();
$sms = $status->of($batchNumber)->get(); // Return an instance of SmsMessage

$isDelivered = $sms->isDelivered(); // Returns true if SMS has been delivered to recipient.
```

#### Get Status of many SMS at a go

```php
require 'path/to/vendor/autoload.php'; // Not needed if using the package inside a framework.

use Prinx\Txtconnect\SmsStatus;

$status = new SmsStatus();

$status->of([$batchNumber1, $batchNumber2, $batchNumber3]);

$sms1 = $status->get($batchNumber1);
$sms1IsDelivered = $sms1->isDelivered();

$sms2 = $status->get($batchNumber2);
$sms2IsDelivered = $sms2->isDelivered();

$sms3 = $status->get($batchNumber3);
$sms3IsDelivered = $sms3->isDelivered();
```

##### Adding batch number one by one

Batch numbers can be added one by one instead of passing all as an array to the `of` method:

```php
$status = new SmsStatus();

$status->of($batchNumber1);
$status->of($batchNumber2);
$status->of($batchNumber3);
```

##### Fluent interface

The `SmsStatus` class implement a fluent interface, so the `of` method can be chained:

```php
$status = new SmsStatus();

$status->of($batchNumber1)
    ->of($batchNumber2)
    ->of($batchNumber3);
```

##### Using `first` and `last`

The `SmsStatus` class also extends the SmsBagAbstract, so when retrieveing statuses of more than one batch numbers, the first and last elements can be retrieved using the `first` and `last` method:

```php
$status = new SmsStatus();

$status->of($batchNumber1)
    ->of($batchNumber2)
    ->of($batchNumber3);

$sms1 = $status->first();
$isSms1Delivered = $sms1->isDelivered();

$sms2 = $status->get($batchNumber2);
$isSms2Delivered = $sms2->isDelivered();

$sms3 = $status->last();
$isSms3Delivered = $sms3->isDelivered();
```

##### All the statuses fetched

```php
$allFetchedStatuses = $status->all(); // Array of SmsMessage

$allFetchedStatusesAsArray = $status->toArray();
```

##### Count number of statuses fetched

```php
$numberOfStatusesFetched = $status->count();
```

### Get Balance

```php
require 'path/to/vendor/autoload.php'; // Not needed if using the package inside a framework.

use Prinx\Txtconnect\Balance;

$balance = new Balance();

$amount = $balance->amount();
```

### Get SMS Inbox

```php
require 'path/to/vendor/autoload.php'; // Not needed if using the package inside a framework.

use Prinx\Txtconnect\Inbox;

$inbox = new Inbox();

$inboxCount = $inbox->count(); // Number of SMS in the fetched Inbox.

$allSmsToArray = $inbox->toArray(); // An array of all inbox SMS, each SMS being an array

$allSms = $inbox->all(); // Array of all inbox SMS, each SMS being a SmsMessage instance

$allSms = $inbox->get($phone); // Get an array of all SMS sent to this phone number

$sms = $inbox->nth(2); // Return the second SmsMessage of the inbox

$inbox->refresh(); // Prepare the inbox to be refetched.
```

## License

[MIT](LICENSE)
