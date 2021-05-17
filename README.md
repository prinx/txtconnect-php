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

The number is automatically sanitized, any space, parenthesis, hyphen, is removed. This allows to pass the number without worrying about the correct format.

For example:

- +233 24 24 24 242
- 233(0)24 24 24 242
- 233 24 24-24-242
- etc

The only constraint is the number has to be in an international format. This constraint can be ignored by specifying a default country.

#### Specifying the default country

Specifying a default country allows to send SMS without worrying if the number is in international format or not. The package will automatically resolve and put the number in required format. For example, after specifying the default country as `Ghana`, you can send SMS to the number like 020 00 00 000, without putting it in international format.

```php
$sms = new Sms();

$message = 'Hi';
$phone = '020 00 00 000';

$response = $sms->country('GH')->send($message, $phone);
```

#### Sending SMS to more than one number

You can send SMS to many numbers at once just by adding the numbers as an array to the `send` method:

```php
$sms = new Sms();

$message = 'Hi';
$phones = ['233200000000', '233210000000', '233220000000'];

$response = $sms->send($message, $phones);
```

#### Using the `to` method

The phone numbers can be passed to the sms instance ahead of time, before calling the `send` method:

```php
$sms = new Sms();

$message = 'Hi';
$phone = '233200000000';

$response = $sms->to($phone)->send($message);
```

And for more numbers:

```php
$sms = new Sms();

$message = 'Hi';
$phones = ['233200000000', '233210000000', '233220000000'];

$response = $sms->to($phones)->send($message);
```

or

```php
$sms = new Sms();

$sms->to('233200000000');
$sms->to('233210000001');
$sms->to('233220000002');

$message = 'Hi';

$response = $sms->send($message);
```

or

```php
$sms = new Sms();

$message = 'Hi';

$response = $sms->to('233200000000')
        ->to('233210000001')
        ->to('233220000002')
        ->send($message);
```

#### Handling duplicate

By default, the package handles automatically duplicate numbers and does not send sms to duplicate numbers (unless it is explicitly activated).

##### Sending to duplicate

If you wish to send sms to duplicate numbers, you can activate it by calling the `keepDuplicate` method on the sms instance.

```php
$sms = new Sms();

$sms->to('233200000000');
$sms->to('233200000000');
$sms->to('233220000002');

$message = 'Hi';

$response = $sms->keepDuplicate()->send($message); // Sends to the first number twice then the third number.
```

##### Deactivate sending to duplicate

If you wish to send sms to duplicate numbers, you can activate it by calling the `removeDuplicate` method on the sms instance.

```php
$sms = new Sms();

$sms->to('233200000000');
$sms->to('233200000000');
$sms->to('233220000002');

$message = 'Hi';

$response = $sms->removeDuplicate()->send($message); // Sends to only two
```

#### Invalid numbers

Sms will not be forwarded to invalid numbers.

Invalid numbers are:

- a number for which the package is not able to determine the country;
- a number that is not a phone number;
- a number that cannot receive an sms (a fixed phone number, for example).

This allows you not to waste bandwidth to make HTTP request to numbers that will not get the message you are sending and at the same time allows not to waste you TXTCONNECT balance.

<!-- #### Send SMS as unicode

If the SMS contains UTF8 special characters, you can activate support for UTF8:

```php
$sms = new Sms();

$response = $sms->asUnicode()->send('Hi 😄', '233200000000');
```

#### Send SMS as plain text (deactivate unicode)

You can deactivate sending as unicode by calling:

```php
$sms = new Sms();

$response = $sms->asPlainText()->send('Hi', '233200000000');
``` -->

### SMS response

After sending an SMS, the response let you know if the SMS has been received by TXTCONNECT or if an error happened.

#### Single SMS response

When sending an SMS to only one contact, the `send` method will return an `SmsResponse` instance:

```php
$sms = new Sms();

$response = $sms->send('Hi', '233200000000'); // $response is an SmsResponse instance

// If request received by TXTCONNECT
if ($response->isBeingProcessed()) {
    $batchNumber = $response->getBatchNumber();
    $statusCheckUrl = $response->getStatusCheckUrl();
    $availableBalance = $response->getBalance();

    // ...
} else {
    $error =  $response->getError();
    $rawResponse - $response->getRawResponse();

    // ...
}
```

#### Multiple SMS response (SmsResponseBag)

When sending SMS to multiple contacts, the `send` method will return an `SmsResponseBag` containing the `SmsResponse` of every contact:


```php
$sms = new Sms();

$phone1 = '233200000000';
$phone2 = '233210000001';
$phone3 = '233220000002';

$response = $sms->send('Hi', [$phone1, $phone2, $phone3]); // $response is a SmsResponseBag instance

// Response for the first phone number
$response1 = $response->get($phone1);

if ($response1->isBeingProcessed()) {
    $batchNumber = $response1->getBatchNumber();
    $statusCheckUrl = $response1->getStatusCheckUrl();
    $availableBalance = $response1->getBalance();

    // ...
} else {
    $error =  $response1->getError();
    $rawResponse - $response1->getRawResponse();

    // ...
}

$response2 = $response->get($phone2);
// ...

$response3 = $response->get($phone3);
//...
```

The first and last response can be accessed using the `first` and `last` method:

```php
$response1 = $response->first();
$response3 = $response->last();
```

#### Forcing SmsResponseBag

You can force the `send` method to always return a `SmsResponseBag` weither the SMS has been sent to only one number or multiple numbers by calling the `asBag` method on the sms instance:

```php
$sms = new Sms();

$response = $sms->asBag()->send('Hi', '233200000000'); // Will return a SmsResponseBag

$smsResponse = $response->first();

if ($smsResponse->isBeingProcessed()) {
    // code
}

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
