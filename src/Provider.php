<?php

namespace JeffersonGoncalves\Socialite;

use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

class Provider
{
    /** @var array<int, string> */
    protected array $scopes = [];

    /** @var array<string, mixed> */
    protected array $with = [];

    protected bool $stateless = false;

    final public function __construct(protected string $name) {}

    public static function make(string $name): static
    {
        return new static($name);
    }

    /**
     * @param  array<int, string>  $scopes
     */
    public function scopes(array $scopes): static
    {
        $this->scopes = $scopes;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function with(array $parameters): static
    {
        $this->with = $parameters;

        return $this;
    }

    public function stateless(bool $condition = true): static
    {
        $this->stateless = $condition;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<int, string>
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * @return array<string, mixed>
     */
    public function getWith(): array
    {
        return $this->with;
    }

    public function isStateless(): bool
    {
        return $this->stateless;
    }

    /**
     * Socialite driver configured with this provider's scopes, parameters and state mode.
     */
    public function driver(?string $redirectUrl = null): SocialiteProvider
    {
        $driver = Socialite::driver($this->name);

        // ponytail: scopes/params/stateless only exist on OAuth2 drivers; OAuth1 drivers use their services config as-is
        if ($driver instanceof AbstractProvider) {
            if ($redirectUrl !== null) {
                $driver->redirectUrl($redirectUrl);
            }

            $driver->scopes($this->scopes)->with($this->with);

            if ($this->stateless) {
                $driver->stateless();
            }
        }

        return $driver;
    }
}
