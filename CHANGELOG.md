# Changelog

All notable changes to this project will be documented in this file.

## 1.0.1 - 2026-10-28

Tests now load the package migrations with jeffersongoncalves/pest-plugin-database-migrations, in the order declared by the service provider, instead of copying the .php.stub files by hand. No runtime changes.

## 1.0.0 - 2026-09-27

First release.

### Added

- Fluent `Provider` with scopes, extra parameters, stateless mode and a configured `driver()`
- `SocialiteUserResolver`: linked account lookup, email match, optional registration and custom callbacks
- `SocialAccount` model with encrypted tokens and a publishable `social_accounts` migration
- PHP 8.1+ and Laravel 10 to 13, tested on SQLite, MySQL and PostgreSQL

## [Unreleased]
