## CarlosCGO/nova-google2fa

This package enforces 2FA for Laravel Nova.

## Upgrade from 0.0.7 to 1.0.0

Upgrade guide is available [Here](docs/upgrade_to_1.0.0.md).

## Flow

### Activation

- User gets recovery codes.

![Recovery codes](docs/images/recovery-codes.png)

- User activates 2FA on his device.

![Activate 2FA](docs/images/register.png)

### Verification

- User verifies login with 2FA.

![Enter 2FA](docs/images/enter-code.png)

### Recovery

- If user enters invalid code, recovery button is shown.

![Enter 2FA](docs/images/invalid-code.png)

- User enters recovery code.

![Enter 2FA](docs/images/enter-recovery-code.png)

- User is redirected to activation process.

## Installation

Install via composer

``` bash
$ composer require carloscgo/nova-google2fa
```

Publish config and migrations

``` bash
$ php artisan vendor:publish --provider="CarlosCGO\Google2fa\ToolServiceProvider"
```

Run migrations

``` bash
$ php artisan migrate
```

Add relation to User model
```php
use CarlosCGO\Google2fa\Models\User2fa;

...

/**
 * @return HasOne
 */
public function user2fa(): HasOne
{
    return $this->hasOne(User2fa::class);
}
```

Add middleware to `nova.config`.
```php
[
    ...
    'middleware' => [
        ...
        \CarlosCGO\Google2fa\Http\Middleware\Google2fa::class,
        ...
    ],
]
```

## Security

If you discover any security-related issues, please email the author instead of using the issue tracker.
## Credits 
- [Jani Cerar](https://github.com/janicerar)

## License

MIT license. Please see the [license file](docs/license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/lifeonscreen/nova-google2fa.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/lifeonscreen/nova-google2fa.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/lifeonscreen/nova-google2fa
[link-downloads]: https://packagist.org/packages/lifeonscreen/nova-google2fa
[link-author]: https://github.com/LifeOnScreen
