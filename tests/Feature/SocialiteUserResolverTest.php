<?php

use Illuminate\Support\Facades\DB;
use JeffersonGoncalves\Socialite\Models\SocialAccount;
use JeffersonGoncalves\Socialite\Provider;
use JeffersonGoncalves\Socialite\SocialiteUserResolver;
use JeffersonGoncalves\Socialite\Tests\Fixtures\User;
use Laravel\Socialite\Two\User as OAuthUser;

function oauthUser(array $attributes = []): OAuthUser
{
    return (new OAuthUser)
        ->map(array_merge(['id' => '123', 'name' => 'Jane', 'nickname' => 'jane', 'email' => 'jane@example.com'], $attributes))
        ->setToken('access-token')
        ->setRefreshToken('refresh-token');
}

it('registers a new user when registration is enabled', function () {
    $user = (new SocialiteUserResolver(User::class, registrationEnabled: true))->resolve(Provider::make('github'), oauthUser());

    expect($user->email)->toBe('jane@example.com')
        ->and($user->name)->toBe('Jane')
        ->and(User::query()->count())->toBe(1);
});

it('does not create users when registration is disabled', function () {
    expect((new SocialiteUserResolver(User::class))->resolve(Provider::make('github'), oauthUser()))->toBeNull()
        ->and(User::query()->count())->toBe(0);
});

it('does not register users without an email', function () {
    $resolver = new SocialiteUserResolver(User::class, registrationEnabled: true);

    expect($resolver->resolve(Provider::make('github'), oauthUser(['email' => null])))->toBeNull();
});

it('finds the user through the linked account before the email', function () {
    $linked = User::query()->create(['name' => 'Linked', 'email' => 'old@example.com', 'password' => 'x']);
    User::query()->create(['name' => 'Other', 'email' => 'jane@example.com', 'password' => 'x']);
    SocialAccount::query()->create(['user_id' => $linked->id, 'provider' => 'github', 'provider_id' => '123']);

    expect((new SocialiteUserResolver(User::class))->resolve(Provider::make('github'), oauthUser())->is($linked))->toBeTrue();
});

it('links accounts and stores tokens encrypted', function () {
    $user = User::query()->create(['name' => 'Jane', 'email' => 'jane@example.com', 'password' => 'x']);
    $resolver = new SocialiteUserResolver(User::class);

    $resolver->link($user, Provider::make('github'), oauthUser());
    $resolver->link($user, Provider::make('github'), oauthUser());

    $account = SocialAccount::query()->sole();

    expect($account->user_id)->toBe($user->id)
        ->and($account->token)->toBe('access-token')
        ->and($account->refresh_token)->toBe('refresh-token')
        ->and(DB::table('social_accounts')->value('token'))->not->toBe('access-token');
});

it('matches by email only when social accounts are disabled', function () {
    $user = User::query()->create(['name' => 'Jane', 'email' => 'jane@example.com', 'password' => 'x']);
    $resolver = new SocialiteUserResolver(User::class, socialAccounts: false);

    $resolver->link($user, Provider::make('github'), oauthUser());

    expect($resolver->resolve(Provider::make('github'), oauthUser())->is($user))->toBeTrue()
        ->and(SocialAccount::query()->count())->toBe(0);
});

it('delegates to custom callbacks', function () {
    $resolver = new SocialiteUserResolver(
        User::class,
        registrationEnabled: true,
        createUserUsing: fn ($oauthUser) => User::query()->create(['name' => 'Custom', 'email' => $oauthUser->getEmail(), 'password' => 'x']),
    );

    expect($resolver->resolve(Provider::make('github'), oauthUser())->name)->toBe('Custom');

    $resolver = new SocialiteUserResolver(User::class, resolveUserUsing: fn () => null);

    expect($resolver->resolve(Provider::make('github'), oauthUser()))->toBeNull();
});
