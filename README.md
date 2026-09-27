# Laravel Socialite

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jeffersongoncalves/laravel-socialite.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-socialite)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-socialite/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/jeffersongoncalves/laravel-socialite/actions?query=workflow%3Atests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/jeffersongoncalves/laravel-socialite/pint.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/jeffersongoncalves/laravel-socialite/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/jeffersongoncalves/laravel-socialite.svg?style=flat-square)](https://packagist.org/packages/jeffersongoncalves/laravel-socialite)
[![License](https://img.shields.io/packagist/l/jeffersongoncalves/laravel-socialite.svg?style=flat-square)](LICENSE.md)

UI-agnostic building blocks on top of [Laravel Socialite](https://laravel.com/docs/socialite): a fluent provider definition, user resolution/registration and a `social_accounts` table linking OAuth identities to your users. Bring your own routes and views, or use [Filament Socialite](https://github.com/jeffersongoncalves/filament-socialite) for Filament panels.

Supports PHP 8.1+ and Laravel 10 to 13.

## Installation

You can install the package via composer:

```bash
composer require jeffersongoncalves/laravel-socialite
```

Publish and run the migration (optional, see `socialAccounts` below):

```bash
php artisan vendor:publish --tag="socialite-migrations"
php artisan migrate
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag="socialite-config"
```

Add each provider's credentials to `config/services.php` as usual for Socialite. The `redirect` key is required by Socialite, but you can pass the callback URL at runtime instead:

```php
'github' => [
    'client_id' => env('GITHUB_CLIENT_ID'),
    'client_secret' => env('GITHUB_CLIENT_SECRET'),
    'redirect' => null,
],
```

## Usage

```php
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use JeffersonGoncalves\Socialite\Provider;
use JeffersonGoncalves\Socialite\SocialiteUserResolver;

$github = Provider::make('github')
    ->scopes(['read:user', 'user:email'])
    ->with(['allow_signup' => 'false']);

// Redirect
Route::get('/oauth/github', fn () => $github->driver(route('oauth.callback'))->redirect());

// Callback
Route::get('/oauth/github/callback', function () use ($github) {
    $oauthUser = $github->driver(route('oauth.callback'))->user();

    $resolver = new SocialiteUserResolver(User::class, registrationEnabled: true);

    $user = $resolver->resolve($github, $oauthUser) ?? abort(403);
    $resolver->link($user, $github, $oauthUser);

    Auth::login($user, remember: true);

    return redirect()->intended('/dashboard');
})->name('oauth.callback');
```

### Provider

| Method | Description |
|--------|-------------|
| `scopes(array)` | Extra OAuth scopes. |
| `with(array)` | Extra query parameters for the authorization request. |
| `stateless(bool)` | Disable session state verification (APIs/SPAs only). |
| `driver(?string $redirectUrl)` | The configured Socialite driver. |

`Provider` is meant to be extended by UI layers (labels, icons, colors). Any Socialite driver works, including [Socialite Providers](https://socialiteproviders.com).

### SocialiteUserResolver

```php
new SocialiteUserResolver(
    userModel: User::class,
    socialAccounts: true,          // look up and store links in social_accounts
    registrationEnabled: false,    // create a user when nothing matches
    resolveUserUsing: null,        // fn (SocialiteUser $user, Provider $provider): ?Model — replaces the lookup
    createUserUsing: null,         // fn (SocialiteUser $user, Provider $provider): ?Model — custom registration
);
```

`resolve()` looks up, in order:

1. `resolveUserUsing`, if set, decides alone.
2. A linked row in `social_accounts` (provider + provider id).
3. A user with the same email.
4. If registration is enabled: `createUserUsing` or a default `name`/`email`/random `password` user.

`link()` stores or updates the social account with the OAuth tokens (encrypted at rest). It does nothing when `socialAccounts` is `false`.

> **Security:** step 3 trusts the email returned by the provider. Only enable providers that verify emails, or use `resolveUserUsing` to add your own checks.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jefferson Gonçalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
