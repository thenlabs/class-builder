# ClassBuilder

Dynamic management of classes, traits and interfaces in PHP.

>If you like this project gift us a ⭐.

## Documentation.

1. English (Pending)
2. [Español](doc/es/index.md)

## Installation.

    $ composer require thenlabs/class-builder

>Require PHP >= 7.2

## Example.

The next example shows a way to create dinamically a PHP class using our `ClassBuilder`.

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

## Development.

Clone this repository and install the Composer dependencies.

    $ composer install

### Running the tests.

All the tests of this project was written with our testing framework [PyramidalTests][pyramidal-tests] wich is based on [PHPUnit][phpunit].

Run tests:

    $ composer test

[phpunit]: https://phpunit.de
[pyramidal-tests]: https://github.com/thenlabs/pyramidal-tests
