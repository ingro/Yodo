# Yodo

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]

A set of utility to easily and rapidly create REST api with Laravel.

## Install

Via Composer

```bash
$ composer require ingruz/yodo
```

## Getting started

Suppose you need to create CRUD (or, more precisely BREAD) handlers for a `Post` model, you should first create a `PostController` class inside your `app/Http/Controllers` folder.

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

Of course you can customize most of Yodo's behaviour.

In addition to Controllers there are two other main pieces of Yodo that are Repositories and Transformers. By default Yodo will search for a custom `Repository` in `app/Repositories` folder, and for a custom `Transformer` in `app/Transformers` (in future both paths will be customizable), searching with the name of the controller's resource (for example if the class name is `PostController` it will search for `PostRepository` and `PostTransformer` respectively).

You can always specify them with a custom path or name overriding protected `getRepositoryClass` or `getTransformerClass` methods in your controller.

If you don't specify anything and Yodo can't find a suitable class, it will fall back to default `Repository` and `Trasformer`. In most cases you won't need to customize the Repository but this should happen more ofter for the Transformer, since the default one will just return the model as an array.

### Repository

To create a custom repository, just extend the default one:

```php
<?php
use Ingruz\Yodo\Base\Repository;

class PostRepository extends Repository {}
```

Like the Controller, by default the Repository will search for it's model class in your root name space (so `PostRepository` will look for `App\Post` model). You can always specify by overriding the `getModelClass` method or by passing direcly an instance to the Repository's constructor (also passing a string is supported).

It's possible to customize in many way how a Repository will work, expecially when handling the `getAll` method that it's used by the `index` method of the Controller:

- **static $eagerAssociations** (defaults to []): define a list of associations that will be automatically eager-loaded;

- **static $defaultParams** (defaults to ['limit' => 50]): define an hash of parameters that will be passed to `getAll` method if not specified elsewhere;

- **static $filterParams** (defaults to []): define a list of columns on which will be performed a full-text search when the filter parameter (`q`) is specified;

- **static $queryParamsHandlers** (defaults to []): define an hash of possible query params that could be mapped to proper database column or handled by a closude, more detailed information in the following chapters;

- **static $orderParamsHandlers** (defaults to []): same as `$queryParamsHandler` but for ordering;

- **static $rules**: define a serie of rules to validate the model during create and update phases, more detailed information in the following chapters.

### Transformer

Yodo uses the excellent [Fractal](http://fractal.thephpleague.com/)  library for handling the transformation of Models.

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
