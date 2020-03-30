# Changelog

All notable changes to `laravel-test-watcher` will be documented in this file

## 1.0.0 - 2019-04-01

- Initial release

## 1.0.1 - 2019-04-01

Updated PHP version dependency to 7.2 in composer.json file

## 1.0.2 - 2019-04-01

Removed version number from CLI UI header

## 1.0.3 - 2019-04-01

Bug fixes

## 1.0.4 - 2019-04-02

Fixed issue with missing error output on Windows

## 1.0.5 - 2019-04-04

Fixed issue where Laravel Test Watcher's environment would override PHPUnits environment.

## 1.0.6 - 2019-04-05

Fixed bug where tests with long failure output would get cut off

## 1.0.7 - 2019-04-05

Fixed bug where CLI did not update when all watch annotations has been removed

## 1.0.8 - 2019-04-11

Added composer.lock to repository for Snyk vulnerability testing

## 1.0.9 - 2019-08-31

Added Laravel 6.0 Compatibility 

## 1.0.10 - 2019-12-12

Added Laravel 6.* Compatibility 

## 1.0.11 - 2019-12-12

Bumped up other dependencies for compatibility with Laravel 6.7.0

## 1.0.12 - 2020-03-03

Added Laravel 7 Compatibility

## 1.0.13 - 2020-03-30

Fixed an issue where DotEnv would give an error on Laravel 7, since the way to initialize DotEnv v4 has been changed from DotEnv v3.
The package now supports both versions.
