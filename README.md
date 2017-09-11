# Yodo

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

A set of utility to easily and rapidly create REST api with Laravel.

## Requirements

Yodo requires php 5.6 or above and is designed to work with Laravel 5.2 or above.

## Install

Via Composer

```bash
$ composer require ingruz/yodo
```

## Getting started

Suppose you need to create CRUD (or, more precisely, BREAD) handlers for a `Post` model, you should first create a `PostController` class inside your `app/Http/Controllers` folder.

```php
<?php
use Ingruz\Yodo\Base\Controller;

class PostController extends Controller {}
```

Then you should tell your router to use that controller for handling the Post resource:

```php
Route::resource('posts', PostController::class);
```

And that's it! You now should have a full working BREAD endpoint for the `Post` resource, with enabled support for pagination, filtering, sorting and more!

## Usage

Of course you can customize most of Yodo's behaviour!

In addition to Controllers there are two other main pieces of Yodo that are Repositories and Transformers. By default Yodo will search for a custom `Repository` in `app/Repositories` folder, and for a custom `Transformer` in `app/Transformers` (in future both paths will be customizable), searching with the name of the controller's resource (for example if the class name is `PostController` it will search for `PostRepository` and `PostTransformer` respectively).

You can always specify them with a custom path or name overriding protected `getRepositoryClass` or `getTransformerClass` methods in your controller.

If you don't specify anything and Yodo can't find a suitable class, it will fall back to default `Repository` and `Trasformer`. In most cases you won't need to customize the Repositories but this will happen more ofter for the Transformers, since the default one will just return the model as an array.

### Repository

To create a custom repository, just extend the default one:

```php
<?php
use Ingruz\Yodo\Base\Repository;

class PostRepository extends Repository {}
```

Like the Controller, by default the Repository will search for it's model class in your root name space (so `PostRepository` will look for `App\Post` model). You can always specify by overriding the `getModelClass` method or by passing direcly an instance to the Repository's constructor (also passing the classname as string is supported).

It's possible to customize in many way how a Repository works, expecially when handling the `getAll` method that it's used by the `index` method inside the resource Controller:

- **static $eagerAssociations** (defaults to []): define a list of associations that will be automatically eager-loaded;

- **static $defaultParams** (defaults to ['limit' => 50]): define an hash of parameters that will be passed to `getAll` method if not specified elsewhere;

- **static $filterParams** (defaults to []): define a list of columns on which will be performed a full-text search when the filter parameter (`q`) is specified;

- **static $queryParamsHandlers** (defaults to []): define an hash of possible query params that could be mapped to database column or handled by a closure, more detailed information in the following chapters;

- **static $orderParamsHandlers** (defaults to []): same as `$queryParamsHandler` but for ordering;

- **static $rules**: define a serie of rules to validate the model during create and update phases using Laravel built-in Validator, more detailed information in the following chapters.

#### Handle query's params

Query parameters defined here will be automatically handled by the repository, you can define them as an hash in `$queryParamsHandlers` or by returning an array overriding the method `getQueryParams`:

```php
static $queryParamsHandlers = [
    'writer' => 'writer_id',
    'commentedByUser' => 'comments.user_id'
];

// ...

public function getQueryParams($requestParams) {
    $queryParams = parent::getQueryParams($requestParams);

    $queryParams['after'] = function($query, $params) {
        return $query->whereHas('comments', function($q) use($params) {
            $q->where('created_by', '>=', $params['after']);
        });
    };

    return $queryParams;
}
```

So the values of the hash could be:

- a simple string: maps the query parameter to a column on the model's database table;
- a simple string, but with a dot (.): maps the query parameter to a column of a related model's database table;
- a closure, which are passed the current `$query` object and the array of query parameters, which could be used to handle more advanced situations.

#### Request validation

It is possible to specify an array of [Laravel's validation rules](https://laravel.com/docs/5.5/validation#available-validation-rules) that will be used by the repository to validate the requests before creating and updating actions.

You can define the rules as a plain array:

```php
static $rules = [
    'title' => 'required|min:10',
    'date' => 'date'
];
```

Or just define rules for a specific action:
```php
static $rules = [
    'create' => [
        'date' => 'date
    ]
];
```

Or define general rules that should always be checked and rules specific to an action:
```php
static $rules = [
    'save' => [
        'title' => 'required|min:10'
    ],
    'create' => [
        'date' => 'date'
    ]
];
```

#### Events

A great place where setting up model's events is the `boot` method of the repository:

```php
public function boot() {
    Post::created(function($model) {
        app('notifier')->notifyNewPost($model); // pseudo-code
    });
}
```

#### Public API

- `getModel`: returns an instance of the repository model, useful as a start to create custom methods;
- `getAll($params)`: returns all the rows filtered, ordered and paginated by `$params`;
- `getById($id)`: returns a row by its `$id`;
- `getFirstBy($key, $value, $operand)`: get the first row that match a where clause;
- `getManyBy($key, $value, $operand)`: get all the rows that match a where clause;
- `create($data)`: create a new item with the provided `$data`;
- `update($item, $data)`: update an `$item` (could be an actual instance or a string id) with the provided `$data`;
- `delete($item)`: delete an `$item` from the database.

### Transformer

Yodo uses the excellent [Fractal](http://fractal.thephpleague.com/) library for handling the transformation of Models in json.

To create a custom Transformer just extends Fractal's `TransformerAbstract`:

```php
<?php
use League\Fractal;

class PostTransformer extends Fractal\TransformerAbstract
{
    public function transform(Post $post)
    {
        return [
            ...
        ];
    }
}
```

### Exceptions

Yodo will automatically returns error responses following two exceptions:

- `ModelValidationException`: this exception will be raised if the payload of a create or update request doesn't satisfy the validation rules defined inside the repository. A response will be returned with the validation error messages with the HTTP code 422;
- `ApiLimitNotValidException`: this exception will be raised if a list request contain a limit not valid (above the `$limitCap` defined in the repository or with a limit set to 0 where not allowed). A response will be return with the HTTP code 400.

### Customization

To customize some default aspects of Yodo you will need to use its service provider. If you are on Laravel 5.5 or above you don't have to do anything, Package Discovery will include it for you.

Otherwise just add it to your config/app.php file providers's array:

```php
'providers' => [
    // ...

    Ingruz\Yodo\YodoServiceProvider::class
]
```

The you can publish the yodo.php config file with the command `php artisan vendor:publish`.

Inside you can customize Repositories and Transformers namespace roots and which http error code Yodo will return in case of `ModelValidationException` and `ApiLimitNotValidException`.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

```bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email direcly the author instead of using the issue tracker.

## Credits

- [Ingro][link-author]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/ingruz/yodo.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/ingruz/yodo/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/ingruz/yodo.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/ingruz/yodo.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/ingruz/yodo.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/ingruz/yodo
[link-travis]: https://travis-ci.org/ingruz/yodo
[link-scrutinizer]: https://scrutinizer-ci.com/g/ingruz/yodo/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/ingruz/yodo
[link-downloads]: https://packagist.org/packages/ingruz/yodo
[link-author]: https://github.com/ingro
[link-contributors]: ../../contributors
