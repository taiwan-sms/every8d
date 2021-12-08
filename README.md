# Every8d notifications channel for Laravel 5.3+

[![StyleCI](https://styleci.io/repos/83760327/shield?style=flat)](https://styleci.io/repos/83760327)
[![Build Status](https://travis-ci.org/taiwan-sms/every8d.svg)](https://travis-ci.org/taiwan-sms/every8d)
[![Total Downloads](https://poser.pugx.org/taiwan-sms/every8d/d/total.svg)](https://packagist.org/packages/taiwan-sms/every8d)
[![Latest Stable Version](https://poser.pugx.org/taiwan-sms/every8d/v/stable.svg)](https://packagist.org/packages/taiwan-sms/every8d)
[![Latest Unstable Version](https://poser.pugx.org/taiwan-sms/every8d/v/unstable.svg)](https://packagist.org/packages/taiwan-sms/every8d)
[![License](https://poser.pugx.org/taiwan-sms/every8d/license.svg)](https://packagist.org/packages/taiwan-sms/every8d)
[![Monthly Downloads](https://poser.pugx.org/taiwan-sms/every8d/d/monthly)](https://packagist.org/packages/taiwan-sms/every8d)
[![Daily Downloads](https://poser.pugx.org/taiwan-sms/every8d/d/daily)](https://packagist.org/packages/taiwan-sms/every8d)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/taiwan-sms/every8d/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/taiwan-sms/every8d/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/taiwan-sms/every8d/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/taiwan-sms/every8d/?branch=master)

This package makes it easy to send notifications using [every8d] with Laravel 5.3+.

## Contents

- [Installation](#installation)
    - [Setting up the Every8d service](#setting-up-the-Every8d-service)
- [Usage](#usage)
    - [Available Message methods](#available-message-methods)
- [Changelog](#changelog)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

## Installation

You can install the package via composer:

```bash
composer require taiwan-sms/every8d illuminate/notifications php-http/guzzle6-adapter
```

Then you must install the service provider:

```php
// config/app.php
'providers' => [
    ...
    TaiwanSms\Every8d\Every8dServiceProvider::class,
],
```

### Setting up the Every8d service

Add your Every8d login, secret key (hashed password) and default sender name (or phone number) to
your `config/services.php`:

```php
// config/services.php
...
'every8d' => [
    'user_id'  => env('SERVICES_EVERY8D_USER_ID'),
    'password' => env('SERVICES_EVERY8D_PASSWORD'),
    'sms_host' => env('SERVICES_EVERY8D_SMS_HOST')
],
...
```

## Usage

You can use the channel in your `via()` method inside the notification:

```php
use TaiwanSms\Every8d\Every8dMessage;
use TaiwanSms\Every8d\Every8dChannel;
use Illuminate\Notifications\Notification;

class AccountApproved extends Notification
{
    public function via($notifiable)
    {
        return [Every8dChannel::class];
    }

    public function toEvery8d($notifiable)
    {
        return Every8dMessage::create("Task #{$notifiable->id} is complete!");
    }
}
```

In your notifiable model, make sure to include a routeNotificationForEvery8d() method, which return the phone number.

```php
public function routeNotificationForEvery8d()
{
    return $this->phone;
}
```

### Available methods

`subject()`: Sets a subject of the notification subject.

`content()`: Sets a content of the notification message.

`sendTime()`: Set send time of the notification message.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email recca0120@gmail.com instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [JhaoDa](https://github.com/recca0120)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

# API Only

```bash
composer require taiwan-sms/every8d php-http/guzzle6-adapter
```

## How to use

```php
require __DIR__.'/vendor/autoload.php';

use TaiwanSms\Every8d\Client;

$userId = 'xxx';
$password = 'xxx';

$client = new Client(userId, $password);

$client->credit(); // 取得額度
var_dump($client->send([
    'to' => '09xxxxxxxx',
    'text' => 'test message',
]));
/*
return [
    'credit' => 100.0,
    'sended' => 1,
    'cost' => 1.0,
    'unsend' => 0,
    'batchId' => 'd0ad6380-4842-46a5-a1eb-9888e78fefd8',
];
 */
```
