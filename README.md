# ahmeti-core

01 - Laravel install
```php
$ composer create-project --prefer-dist laravel/laravel project-name
```

02 - Laravel make:auth
```php
$ php artisan make:auth
```

03 - Remove all devDependencies in package.json

04 - ahmeti-core-js NPM install
```php
$ npm i --save-dev ahmeti-core-js --no-audit
```

05 - Ahmeti/Core composer install
```php
$ composer require ahmeti/core:dev-master
$ composer require guzzlehttp/guzzle
$ composer require elibyy/tcpdf-laravel
$ composer require maatwebsite/excel
$ composer require intervention/image
```

06 - Copy Gulp File
```php
$ cp node_modules/ahmeti-core-js/gulpfile.js gulpfile.js
```

07 - Gulp Init
```php
$ gulp init
```

08 - Change User Model Name Space (config/auth.php)
```php
App\Modules\User\Models\User
```

09 - Add Route AuthAjaxRequest Middleware (app/Http/Kernel.php)
```php
protected $routeMiddleware = [
    ...
    'auth.ajax' => \App\Http\Middleware\AuthAjaxRequest::class,
];
```

10 - Make Comment Csrf Token Class
```php
// VerifyCsrfToken::class
```

11 - Change Route Name Space (app/Providers/RouteServiceProvider.php)
```php
protected $namespace = 'App\Modules';
```

12 - Check render Function (app/Exceptions/Handler.php)
```php
public function render($request, Exception $exception)
{
    // Check
}
```

13 - NPM run
```php
$ npm run dev
```