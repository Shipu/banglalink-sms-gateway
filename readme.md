<h1 align="center">Banglalink SMS GATEWAY</h1>

<p align="center">
    A PHP client for Banglalink SMS Gateway API. This package is also support Laravel and Lumen.
</p>

## Installation

Go to terminal and run this command

```shell
composer require shipu/banglalink-sms-gateway
```

Wait for few minutes. Composer will automatically install this package for your project.

### For Laravel

Below **Laravel 5.5** open `config/app` and add this line in `providers` section

```php
Shipu\BanglalinkSmsGateway\BanglalinkServiceProvider::class,
```

For Facade support you have add this line in `aliases` section.

```php
'Banglalink'   =>  Shipu\BanglalinkSmsGateway\Facades\Banglalink::class,
```

Then run this command

```shell
php artisan vendor:publish --provider="Shipu\BanglalinkSmsGateway\BanglalinkServiceProvider"
```

## For PHP

This package is required two configurations.

1. user_id = your user_id which provide by Banglalink.
2. password = your password which provide by Banglalink.

banglalink-sms-gateway is take an array as config file. Lets services

```php
use Shipu\BanglalinkSmsGateway\Banglalink;

$config = [
    'user_id' => 'Your User Id',
    'password' => 'Your Password'
];

$sms = new Banglalink($config);
```
### For Laravel

This package is also support Laravel. For laravel you have to configure it as laravel style.

Go to `config/banglalink-sms-gateway.php` and configure it with your credentials.

```php
return [
    'user_id' => 'Your User id',
    'password' => 'Your Password'
];
```

## Usages
Its very easy to use. This packages has a lot of functionality and features.

### Send SMS to a single user

**In PHP:**
```php
use \Shipu\BanglalinkSmsGateway\Services\Banglalink;

...

$sms = new Banglalink($config);
$response = $sms->message('your text here !!!', '01606022000')->send(); // Guzzle Response with request data

// For another example please see below laravel section. 
 
return $response->autoParse(); // Getting only response contents.
```
**In Laravel:**
```php
use \Shipu\BanglalinkSmsGateway\Facades\Banglalink;

...

$sms = Banglalink::message('your text here !!!', '01606022000')->send(); // Guzzle Response with request data

// or

$sms = Banglalink::message('your text here !!!')->to('01606022000')->send();

// or

$sms = Banglalink::send(
    [
        'message' => "your text here",
        'to' => '01616022000'
    ]
);
return $sms->autoParse(); // Getting only response contents.
```

### Send same message to all users
```php
$sms = Banglalink::message('your text here !!!')
            ->to('01616022669')
            ->to('01845736124')
            ->to('01745987364')
            ->send();
            
// or you can try below statements also

$sms = Banglalink::message('your text here !!!', '01616022669')
            ->to('01845736124')
            ->to('01745987364')
            ->send();
            
// or           

$users = [
    '01616022669',
    '01845736124',
    '01745987364'
];        
$sms = Banglalink::message('your text here !!!',$users)->send(); 
```

### Send SMS to more user
```php
$sms = Banglalink::message('your text here one !!!')->to('01616022669')
            ->message('your text here two !!!')->to('01845736124')
            ->message('your text here three !!!')->to('01745987364')
            ->send();
// or

$sms = Banglalink::message('your text here one !!!', '01616022669')
            ->message('your text here two !!!', '01845736124')
            ->message('your text here three !!!', '01745987364')
            ->send();
            
// or 

$sms = Banglalink::send([
    [
        'message' => "your text here one !!!",
        'to' => '01616022669'
    ],
    [
        'message' => "your text here two !!!",
        'to' => '01707722669'
    ],
    [
        'message' => "your text here three !!!",
        'to' => '01745987364'
    ]
]);

// or 

$sms = Banglalink::message('your text here one !!!', '01616022669')->send([
    [
        'message' => "your text here two !!!",
        'to' => '01707722669'
    ],
    [
        'message' => "your text here three !!!",
        'to' => '01745987364'
    ]
]);         
```

### Send SMS with SMS template
Suppose you have to send SMS to multiple users but you want to mentions their name dynamically with message. So what can you do? Ha ha this package already handle this situations. Lets see
```php
$users = [
    ['01670420420', ['Nahid', '1234']],
    ['01970420420', ['Rana', '3213']],
    ['01770420420', ['Shipu', '5000']],
    ['01570420420', ['Kaiser', '3214']],
    ['01870420420', ['Eather', '7642']]
]
$sms = new \Shipu\BanglalinkSmsGateway\Services\Banglalink(config('banglalink-sms-gateway'));
$msg = $sms->message("Hello %s , Your promo code is: %s", $users)->send();

// or 

$users = [
    '01670420420' => ['Nahid', '1234'],
    '01970420420' => ['Rana', '3213'],
    '01770420420' => ['Shipu', '5000'],
    '01570420420' => ['Kaiser', '3214'],
    '01870420420' => ['Eather', '7642']
]
$sms = new \Shipu\BanglalinkSmsGateway\Services\Banglalink(config('banglalink-sms-gateway'));
$msg = $sms->message("Hello %s , Your promo code is: %s", $users)->send();
```

Here this messege will sent as every users with his name and promo code like:

- `8801670420420` - Hello Nahid , Your promo code is: 1234
- `8801970420420` - Hello Rana , Your promo code is: 3213
- `8801770420420` - Hello Shipu , Your promo code is: 5000
- `8801570420420` - Hello Kaiser , Your promo code is: 1234
- `8801870420420` - Hello Eather , Your promo code is: 7642

### Change Number Prefix
```php
$sms = Banglalink::numberPrefix('91')->message('your text here !!!', '01606022000')->send();
```
Default number prefix is `88`; 

### Send sms with sender name
```php
$sms = Banglalink::sender('XYZ Company')->message('your text here !!!', '01606022000')->send();
```
Default sender name is `null`. for details please contact to banglalink customer support; 

### Debugging
```php
$sms = Banglalink::debug(true)->message('your text here !!!', '01606022000')->send(); // // debug true or blank.
```
Default value is `false`. When debug `true` it's stop sending SMS and return sending query strings.

### Response Data auto parse
```php
$sms = Banglalink::autoParse(true)->message('your text here !!!', '01606022000')->send(); // autoParse true or blank.
```
Default value is `false`.

### Disable Template
```php
$sms = Banglalink::template(false)->message('your text here !!!', '01606022000')->send();
```
Default value is `true`.

### Response Data
```php
dd($sms);
```
Response :
```php
Response {#463 ▼
  #response: Response {#446 ▶}
  #request: Request {#428 ▼
    -method: "GET"
    -requestTarget: null
    -uri: Uri {#429 ▶}
    -headers: []
    -headerNames: []
    -protocol: "1.1"
    -stream: null
    +"details": array:3 [▼
      "url" => "https://vas.banglalinkgsm.com/sendSMS/sendSMS"
      "method" => "GET"
      "parameters" => array:1 [▼
        "query" => array:5 [▶]
      ]
    ]
  }
  #contents: "Success Count : 2 and Fail Count : 0"
}
```
Response auto parse: 
```php
dd($sms->autoParse());
```
Response
```
"Success Count : 2 and Fail Count : 0"
```

### Response Details
```php
$sms = Banglalink::details()->message('your text here !!!', '01606022000')->send();
```
Response: 
```
[
    'success' => 1,
    'failed' => 0
]
```
