<?php

use JeffersonGoncalves\Socialite\Provider;

it('configures the socialite driver', function () {
    $location = Provider::make('github')
        ->scopes(['read:user'])
        ->with(['allow_signup' => 'false'])
        ->stateless()
        ->driver('https://app.test/oauth/github/callback')
        ->redirect()
        ->getTargetUrl();

    expect($location)
        ->toStartWith('https://github.com/login/oauth/authorize')
        ->toContain(urlencode('https://app.test/oauth/github/callback'))
        ->toContain(urlencode('read:user'))
        ->toContain('allow_signup=false')
        ->not->toContain('state=');
});
