# Shopware APP SDK for PHP

[![codecov](https://codecov.io/gh/shopware/app-php-sdk/branch/main/graph/badge.svg?token=3J0I167SBI)](https://codecov.io/gh/shopware/app-php-sdk)
[![PHPUnit](https://github.com/shopware/app-php-sdk/actions/workflows/phpunit.yml/badge.svg)](https://github.com/shopware/app-php-sdk/actions/workflows/phpunit.yml)
[![PHPStan](https://github.com/shopware/app-php-sdk/actions/workflows/phpstan.yml/badge.svg)](https://github.com/shopware/app-php-sdk/actions/workflows/phpstan.yml)
[![CS-Fixer](https://github.com/shopware/app-php-sdk/actions/workflows/cs-fixer.yml/badge.svg)](https://github.com/shopware/app-php-sdk/actions/workflows/cs-fixer.yml)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fshopware%2Fapp-php-sdk%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/shopware/app-php-sdk/main)

This SDK is independent of any Framework. It uses PSR Request/Response/HttpClient to be usable cross framework.

## Featuers

- Registration Flow
- Lifecycle Handling (app activated, deactivated, uninstalled)
- Convert Actions (Webhook, ActionButton, Payment, ...) into Structs
- Framework agnostic (PSR Request/Response/HttpClient/Repository)
- Events

## Symfony Bundle

If you are using Symfony, you can use the [Symfony Bundle](https://github.com/shopware/AppBundle) to integrate the SDK.
See Getting Started of the Bundle for more information.

## Documentation

- [Getting Started](./docs/01-getting_started.md)
- [Lifecycle](./docs/02-lifecycle.md)
- [Context](./docs/03-context.md)
- [Signing](./docs/04-signing.md)
- [HTTP Client](./docs/05-http-client.md)
- [Events](./docs/06-events.md)

## Example

Checkout the [Example project](./examples) for a simple working example.