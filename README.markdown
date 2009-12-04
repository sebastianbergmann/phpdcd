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
* Infering the type of a variable is limited to type-hinted arguments (`function foo(Bar $bar) {}`) and direct object creation (`$object = new Clazz`)

Installation
------------

phpdcd should be installed using the [PEAR Installer](http://pear.php.net/). This installer is the backbone of PEAR, which provides a distribution system for PHP packages, and is shipped with every release of PHP since version 4.3.0.

The PEAR channel (`pear.phpunit.de`) that is used to distribute phpdcd needs to be registered with the local PEAR environment:

    sb@ubuntu ~ % pear channel-discover pear.phpunit.de
    Adding Channel "pear.phpunit.de" succeeded
    Discovery of channel "pear.phpunit.de" succeeded

This has to be done only once. Now the PEAR Installer can be used to install packages from the PHPUnit channel:

    sb@ubuntu ~ % pear install phpunit/phpdcd-beta
    downloading phpdcd-0.9.0.tgz ...
    Starting to download phpdcd-0.9.0.tgz (6,235 bytes)
    .....done: 6,235 bytes
    downloading PHP_TokenStream-0.9.0.tgz ...
    Starting to download PHP_TokenStream-0.9.0.tgz (5,145 bytes)
    ...done: 5,145 bytes
    install ok: channel://pear.phpunit.de/PHP_TokenStream-0.9.0
    install ok: channel://pear.phpunit.de/phpdcd-0.9.0

After the installation you can find the phpdcd source files inside your local PEAR directory; the path is usually `/usr/lib/php/PHPDCD`.
