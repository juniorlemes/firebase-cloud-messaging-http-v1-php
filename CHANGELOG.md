# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]

## [0.3.1] - 2026-11-24
### Fixed
- `FCM::send()`, `FCM::subscribeToTopic()` and `FCM::unsubscribeFromTopic()` discarded errors returned by Google. They now return `true` on success or the error message (`true|string`, previously `void`).

## [0.3.0] - 2026-10-24
### Changed
- Requires PHP `^8.5.12`.
- Requires `firebase/php-jwt` `^7.0` (6.x is affected by a security advisory).
- Dev dependencies updated: Pest 5, PHPStan 2, Rector 2, Pint 1.32, Symfony VarDumper 8.
- Rector set raised to `UP_TO_PHP_85`.
- Added return and parameter types to the public API (`FCM::send(): void`, `FCM::subscribeToTopic(): void`, `FCM::unsubscribeFromTopic(): void`, `FCM::getBearerToken(): string`, `Client::send(): true|string`, `Client::post(): true|string`, `Credentials::setKeyFilePath(string)`, among others).
- `Credentials` validates the key file and token response, throwing a clear exception when a field is missing or invalid.
- `Client::post()` returns `"HTTP <status>"` when an error response has no `error.message`.
- Added `pint.json` (Laravel preset, keeping `new Class()` parentheses).

### Fixed
- Double slash in the topic management URLs (`subscribeToTopic` and `unsubscribeFromTopic`).

## [0.2.0]
- Adds support for PHP 8 and `firebase/php-jwt` `^6.10`.

## [0.1.0] and earlier
- Adds first version
