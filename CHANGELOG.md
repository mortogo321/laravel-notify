# Changelog

All notable changes to `laravel-notify` will be documented in this file.

## [2.0.0] - 2026-02-08

### Added
- LINE Notify provider with sticker, image, and status support
- `get()` helper method on `AbstractProvider` for HTTP GET requests

### Changed
- **BREAKING**: Minimum PHP version is now 8.2 (dropped 8.1)
- **BREAKING**: Minimum Laravel version is now 11.x (dropped 10.x)
- Replaced Guzzle HTTP client with Laravel's built-in `Http` facade
- Updated `orchestra/testbench` to `^9.0|^10.0`
- Updated `phpunit/phpunit` to `^11.0|^12.0`
- Updated `phpunit.xml.dist` to PHPUnit 11 format (`<source>` instead of `<coverage>`)

### Removed
- `guzzlehttp/guzzle` dependency (replaced by `illuminate/http`)
- `GuzzleHttp\Client` property from `AbstractProvider`
- `handleGuzzleException()` method from `AbstractProvider`

## [1.0.0] - 2025-01-20

### Added
- Initial release
- Support for Slack notifications with webhooks
- Support for Discord notifications with webhooks
- Support for Telegram notifications via Bot API
- Support for Email notifications using Laravel Mail
- Multi-provider notification support
- Configurable providers via config file and environment variables
- Extensible provider system for custom integrations
- Facade support for easy access (`Notify::send()`)
- Comprehensive documentation and usage examples
- PHPUnit test suite foundation
