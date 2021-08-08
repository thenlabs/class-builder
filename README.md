<div align="center">
    <a href="https://github.com/thenlabs/class-builder/blob/v1/LICENSE.txt" target="_blank">
        <img src="https://img.shields.io/github/license/thenlabs/components?style=for-the-badge">
    </a>
    <img src="https://img.shields.io/packagist/php-v/thenlabs/components?style=for-the-badge">
    <a href="https://travis-ci.com/github/thenlabs/components" target="_blank">
        <img src="https://img.shields.io/travis/com/thenlabs/components?style=for-the-badge">
    </a>
    <a href="https://twitter.com/ThenLabsOrg" target="_blank">
        <img src="https://img.shields.io/twitter/follow/thenlabs?style=for-the-badge">
    </a>
</div>

<br>

<h1 align="center">ClassBuilder</h1>
<h3 align="center">Dynamic management of classes, traits and interfaces in PHP.</h3>

<br>

The next example shows a way to create dinamically a PHP class using our `ClassBuilder`. Learn more in the documentation.

```php
<?php

use ThenLabs\ClassBuilder\ClassBuilder;

$personClass = new ClassBuilder('Person');
$personClass->setNamespace('ThenLabs\Demo');

$personClass->addProperty('name')->setAccess('protected');

$personClass->addMethod('__construct', function (string $name) {
    $this->name = $name;
});

$personClass->addMethod('getName', function (): string {
    return $this->name;
});

$personClass->install();

$andy = new Person('Andy');

$andy->getName() === 'Andy';            // true
$andy instanceof \ThenLabs\Demo\Person; // true
```

## 📖 Documentation.

1. 🇬🇧 English (Pending)
2. [🇪🇸 Español](https://thenlabs.org/es/doc/components/master/index.html)

## 🧪 Running the tests.

All the tests of this project was written with our testing framework [PyramidalTests][pyramidal-tests] wich is an extension of [PHPUnit][phpunit].

After clone this repository, install the Composer dependencies:

    $ composer install

Run PHPUnit:

    $ ./vendor/bin/phpunit

[phpunit]: https://phpunit.de
[pyramidal-tests]: https://github.com/thenlabs/pyramidal-tests

If you want to run the tests with a specific version of PHP, it is possible to use Docker as follows:

    $ docker run -it --rm -v "$PWD":/usr/src/myapp -w /usr/src/myapp php:7.2-cli php vendor/bin/phpunit

>Change 7.2 for the desired PHP version.
