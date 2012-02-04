phpdcd
======

**phpdcd** is a Dead Code Detector (DCD) for PHP code. It scans a PHP project for all declared functions and methods and reports those as being "dead code" that are not called at least once.

Limitations
-----------

As PHP is a very dynamic programming language, the static analysis performed by **phpdcd** does not recognize function or method calls that are performed using one of the following language features:

* Reflection API
* `call_user_func()` and `call_user_func_array()`
* Usage of the `new` operator with variable class names
* Variable class names for static method calls such as `$class::method()`
* Variable function or method names such as `$function()` or `$object->$method()`
* Automatic calls to methods such as `__toString()` or `Iterator::*()`

Also note that infering the type of a variable is limited to type-hinted arguments (`function foo(Bar $bar) {}`) and direct object creation (`$object = new Clazz`)

Installation
------------

`phpdcd` should be installed using the PEAR Installer, the backbone of the [PHP Extension and Application Repository](http://pear.php.net/) that provides a distribution system for PHP packages.

Depending on your OS distribution and/or your PHP environment, you may need to install PEAR or update your existing PEAR installation before you can proceed with the following instructions. `sudo pear upgrade PEAR` usually suffices to upgrade an existing PEAR installation. The [PEAR Manual ](http://pear.php.net/manual/en/installation.getting.php) explains how to perform a fresh installation of PEAR.

The following two commands (which you may have to run as `root`) are all that is required to install `phpdcd` using the PEAR Installer:

    pear config-set auto_discover 1
    pear install pear.phpunit.de/phpdcd

After the installation you can find the `phpdcd` source files inside your local PEAR directory; the path is usually `/usr/lib/php/PHPDCD`.
