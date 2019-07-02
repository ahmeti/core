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
$ npm i --save-dev ahmeti-core-js
```

05 - Ahmeti/Core composer install
```php
$ composer require ahmeti/core:dev-master
```

06 - Copy Gulp File
```php
$ cp node_modules/ahmeti-core-js/gulpfile.js gulpfile.js
```

07 - Gulp Init
```php
$ gulp init
```

08 - Change User Model Name Space
```php
config/auth.php
```

09 - Add Route AuthAjaxRequest Middleware (app/Http/Kernel.php)
```php
protected $routeMiddleware = [
    ...
    'auth.ajax' => \App\Http\Middleware\AuthAjaxRequest::class,
];
```

09 - Change Route Name Space (app/Providers/RouteServiceProvider.php)
```php
protected $namespace = 'App\Modules';
```
