![Logo](logo.jpg)
# Run specific tests methods when your test or source code changes

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wackystudio/laravel-test-watcher.svg?style=flat-square)](https://packagist.org/packages/wackystudio/laravel-test-watcher)
[![Build Status](https://travis-ci.org/WackyStudio/laravel-test-watcher.svg?branch=master&style=flat-square)](https://travis-ci.org/WackyStudio/laravel-test-watcher.svg?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/wackystudio/laravel-test-watcher.svg?style=flat-square)](https://packagist.org/packages/wackystudio/laravel-test-watcher)
[![Known Vulnerabilities](https://snyk.io/test/github/{username}/{repo}/badge.svg?style=flat-square)](https://snyk.io/test/github/wackystudio/laravel-test-watcher)
When looking at our testing workflow, we realized how often we were triggering our tests, especially single test cases or groups of test cases.
Using an IDE like PHPStorm this is done quickly with a keyboard shortcut, but in other IDEs, or editors, this is not always as easy. 
Therefore we have built Laravel Test Watcher.

Instead of running your entire test suite or having to group your tests, Laravel Test Watcher can watch 
test cases you annotate with a `@watch` annotation.

You start the watcher through the `tests:watch` artisan command. 
As soon as you save a test file with a `@watch` annotation on a test case, 
Laravel Test Watcher automatically notice that you have added the annotation 
and run the test case for every change in your source code.

When you are finished testing the test case, you can tell Laravel Test Watcher 
to stop watching the test case by removing the `@watch` annotation again; it is as easy as that.

No need to jump between your IDE/editor and the terminal, adding or removing `@watch` annotations in your code is enough, 
and Laravel Test Watcher takes care of the rest.

## Installation

You can install the package via composer:

```bash
composer require wackystudio/laravel-test-watcher
```

## Usage
To watch a test in a test class, use the @watch annotation like this:
``` php
/**
* @test
* @watch
*/
public function it_runs_annotated_tests_in_this_test_file()
{
    //...
}
```
If you are not using a `@test` annotation but are adding test to your test methods name, you can watch the test case like this:
``` php
/**
* @watch
*/
public function test_it_runs_annotated_tests_every_time_source_code_changes()
{
    //...
}
```
To watch tests and source file for changes, run the test watcher through Laravel Artisan like this:
```bash
php artisan tests:watch
```

**NOTICE:**
For database testing we recommend that you create a `.env.testing` environment file with details for a dedicated testing database. 
If you don't do this, Laravel Test Watcher will test against the database given in the `.env` file, which we do not recommend.

### Configuration
By default Laravel Test Watcher watches all files in the `app` `routes` and `tests` folders, 
meaning that any changes to a file in these directories, makes Laravel Test Watcher run all the watched test cases.

If you want to configure which directories Laravel Test Watcher should watch, you can do this by publishing the configuration file
through the `vendor:publish` artisan command like this:
```bash
php artisan vendor:publish
```
publish the configuration file for Laravel Test Watcher only or select the config tag to publish configuration files, for all packages in your Laravel Application.

### Limitations
Even though Laravel Test Watcher can watch as many tests as you like, 
it is not the intention that you should use it on every single test case in your test suite but instead, use it on the tests for the current feature you are implementing.

Since it is not possible to tell PHPUnit to run multiple single test cases so all test cases can be tested in a single PHPUnit session, each test case is running in its own PHPUnit session, which makes the execution of the tests a bit slower. 

If you need to run all your tests, we recommend you run a good old:
```bash
./vendor/bin/phpunit
```
This will run through all of your tests in your test suite much faster.

When starting Laravel Test Watcher through the artisan command, it bootstraps the entire Laravel application and loads the environment variables defined in the `.env` file. 
This gives us some issues since PHPUnit does not override the loaded environment variables when running tests which make each test run with the environment variables already loaded, 
instead of the testing environment variables it should be using. 
To mitigate this, Laravel Test Watcher requires a `.env.testing` file where all your environment variables for your testing setup is defined. 
This is then used to override the environment variables when Laravel Test Watcher has been instantiated. 
Unfortunately, this means that you cannot use the environment variables you have defined in your `phpunit.xml` file.

### Testing
``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email tgn@wackystudio.com instead of using the issue tracker.

## Credits

- [Thomas Nørgaard](https://github.com/thomasnoergaard)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).