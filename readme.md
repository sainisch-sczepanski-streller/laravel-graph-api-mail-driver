# Laravel Graph API Mail Driver

Send Emails via Microsoft Graph API from your Laravel 10 Project.

## Description
Minimal Mail driver to send E-Mails via Microsoft Graph API in Laravel.

Don't forget the app permissions and policies in the Graph API. 
For further info see:
[Microsoft Docs](https://learn.microsoft.com/en-us/graph/api/user-sendmail?view=graph-rest-1.0&tabs=http)

## Getting started
### install
```php
composer install sainisch-sczepanski-streller/laravel-graph-api-mail-driver
```
#### .env
```php
MAIL_MAILER=microsoft-graph-api
MAIL_MS_GRAPH_CLIENT_ID=<your client id>
MAIL_MS_GRAPH_CLIENT_SECRET=<your client secret>
MAIL_MS_GRAPH_TENANT_ID=<your tenant id>
MAIL_MS_GRAPH_SAVE_TO_SEND_ITEMS=<bool>
```
#### config/mail.php
```php
'mailers' => [

        'microsoft-graph-api' => [
            'transport' => 'microsoft-graph-api',
            'client_id' => env('MAIL_MS_GRAPH_CLIENT_ID'),
            'client_secret' => env('MAIL_MS_GRAPH_CLIENT_SECRET'),
            'tenant_id' => env('MAIL_MS_GRAPH_TENANT_ID'),
            'saveToSentItems' => env('MAIL_MS_GRAPH_SAVE_TO_SEND_ITEMS', true),
        ],
...
```
## Usage
Use it like any other Laravel mail driver
```php
 Mail::to([<recipients>])->send(<your mail class>);
```

## Compatibility
Build and tested in Laravel 10

## Contributors
- [Steven Streller](https://github.com/StevenStreller)
- [Adrian Sczepanski](https://github.com/Skysagit)
- [Hanno Sainisch](https://github.com/HannoSainisch)

## License
The MIT License (MIT). Please see license file for more information.

