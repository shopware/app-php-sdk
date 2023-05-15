# Shopware APP SDK for PHP

[![codecov](https://codecov.io/gh/shopware/app-php-sdk/branch/main/graph/badge.svg?token=3J0I167SBI)](https://codecov.io/gh/shopware/app-php-sdk)
[![PHPUnit](https://github.com/shopware/app-php-sdk/actions/workflows/phpunit.yml/badge.svg)](https://github.com/shopware/app-php-sdk/actions/workflows/phpunit.yml)
[![PHPStan](https://github.com/shopware/app-php-sdk/actions/workflows/phpstan.yml/badge.svg)](https://github.com/shopware/app-php-sdk/actions/workflows/phpstan.yml)
[![CS-Fixer](https://github.com/shopware/app-php-sdk/actions/workflows/cs-fixer.yml/badge.svg)](https://github.com/shopware/app-php-sdk/actions/workflows/cs-fixer.yml)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fshopware%2Fapp-php-sdk%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/shopware/app-php-sdk/main)

This SDK is independent of any Framework. It uses PSR Request/Response/HttpClient to be usable cross framework.

## Features

- Registration flow
- Lifecycle handling (app activated, deactivated, uninstalled)
- Convert actions (Webhook, ActionButton, Payment, ...) into structs
- Framework agnostic (PSR Request/Response/HttpClient/Repository)
- Events

## Symfony Bundle

If you are using Symfony, you can use the [Symfony Bundle](https://github.com/shopware/AppBundle) to integrate the SDK. To get started with it, refer to the below documentation.

## Documentation

- [Getting Started](https://developer.shopware.com/docs/guides/plugins/apps/app-sdks/php/01-getting_started)
- [Lifecycle](https://developer.shopware.com/docs/guides/plugins/apps/app-sdks/php/02-lifecycle)
- [Context](https://developer.shopware.com/docs/guides/plugins/apps/app-sdks/php/03-context)
- [Signing](https://developer.shopware.com/docs/guides/plugins/apps/app-sdks/php/04-signing)
- [HTTP Client](https://developer.shopware.com/docs/guides/plugins/apps/app-sdks/php/05-http-client)
- [Events](https://developer.shopware.com/docs/guides/plugins/apps/app-sdks/php/06-events)

## Example

Checkout the [example project](./examples) for a simple working example.
