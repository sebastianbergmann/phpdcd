[![Latest Stable Version](https://poser.pugx.org/sebastian/phpdcd/v/stable.png)](https://packagist.org/packages/sebastian/phpdcd)
[![Build Status](https://travis-ci.org/sebastianbergmann/phpdcd.png?branch=master)](https://travis-ci.org/sebastianbergmann/phpdcd)

# PHP Dead Code Detector (PHPDCD)

**phpdcd** is a Dead Code Detector (DCD) for PHP code. It scans a PHP project for all declared functions and methods and reports those as being "dead code" that are not called at least once.

## Limitations

As PHP is a very dynamic programming language, the static analysis performed by **phpdcd** does not recognize function or method calls that are performed using one of the following language features:

* Reflection API
* `call_user_func()` and `call_user_func_array()`
* Usage of the `new` operator with variable class names
* Variable class names for static method calls such as `$class::method()`
* Variable function or method names such as `$function()` or `$object->$method()`
* Automatic calls to methods such as `__toString()` or `Iterator::*()`

Also note that infering the type of a variable is limited to type-hinted arguments (`function foo(Bar $bar) {}`) and direct object creation (`$object = new Clazz`)

## Installation

### PHP Archive (PHAR)

The easiest way to obtain PHPDCD is to download a [PHP Archive (PHAR)](http://php.net/phar) that has all required dependencies of PHPDCD bundled in a single file:

    wget https://phar.phpunit.de/phpdcd.phar
    chmod +x phpdcd.phar
    mv phpdcd.phar /usr/local/bin/phpdcd

You can also immediately use the PHAR after you have downloaded it, of course:

    wget https://phar.phpunit.de/phpdcd.phar
    php phpdcd.phar

### Composer

Simply add a dependency on `sebastian/phpdcd` to your project's `composer.json` file if you use [Composer](http://getcomposer.org/) to manage the dependencies of your project. Here is a minimal example of a `composer.json` file that just defines a development-time dependency on PHPDCD:

    {
        "require-dev": {
            "sebastian/phpdcd": "*"
        }
    }

For a system-wide installation via Composer, you can run:

    composer global require 'sebastian/phpdcd=*'

Make sure you have `~/.composer/vendor/bin/` in your path.

