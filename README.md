
# ClassBuilder

*ClassBuilder* sirve para construir clases PHP de manera 100% dinámica. Como se puede ver en el ejemplo de más abajo, para crear una nueva clase dinámica, se debe crear una instancia de `NubecuLabs\ClassBuilder\ClassBuilder` a través de la cual se deberá especificar toda la definición de la nueva clase.

Esta manera de crear clases no viene a sustituir **en ningún caso** la manera estándar que ofrece el lenguaje. No obstante, en casos específicos puede ofrecer ciertas ventajas.

## Ventajas.

1. Se puede emplear como alternativa a las clases anónimas de PHP superando las limitaciones de las mismas dado que estas clases dinámicas son a fin de cuenta clases definidas por el usuario.

2. Evita tener que crear clases en archivos cuando se está probando ciertos comportamientos a partir del diseño de una clase. Por ejemplo, en el caso de los *frameworks* es común que se interpreten ciertas funcionalidades a partir de anotaciones sobre clases, métodos y/o propiedades. Para probar cada funcionalidad se deberá contar con una clase que posea un diseño específico. En esos casos, el empleo de `ClassBuilder` evitaría tener que crear un archivo por cada clase que se necesite probar para un diseño específico.

## Desventajas.

1. Puede afectar en sentido general la legibilidad del código.

## Instalación

Se require PHP >= 7.2.

    $ composer require nubeculabs/class-builder dev-master

>Si se va a emplear para pruebas unitarias sobre el diseño de clases, es muy recomendable que especifique la opción `--dev`.

## Ejemplo de creación de clase dinámica.

```php

use NubecuLabs\ClassBuilder\ClassBuilder;

(new ClassBuilder('MyClass'))
    ->setNamespace('MyNamespace')

    ->extends(MyParentClass::class)

    ->implements(Interface1::class, Interface2::class, Interface3::class)

    ->use(Trait1::class, ['method1 as methodAlias1'])

    ->use(
        [Trait2::class, Trait3::class],
        [Trait3::class.'::method2 insteadof '.Trait2::class]
    )

    ->addComments('@My\Annotation1', '@My\Annotation2')

    ->addConstant('MY_CONSTANT1')
        ->setAccess('private')
        ->setValue(100)
    ->end()

    ->addProperty('property1')
        ->setAccess('protected')
        ->setValue('default value for the property')
        ->addComments('@My\Annotation1', '@My\Annotation2')
        ->addComment('@My\Annotation3')
    ->end()

    ->addMethod('__construct', function ($arg1, $arg2) {
        // ...
    })->end()

    ->addMethod('method1')
        ->setAccess('protected')
        ->addComment('@My\Annotation3')
        ->setClosure(function (string $arg1, ?int $arg2): ?string {
            // ...
        })
    ->end()

    ->addMethod('method1')
        ->setStatic(true)
        ->setAccess('public')
        ->setClosure(function (string $arg1, ?int $arg2): ?string {
            // ...
        })
    ->end()
->install();
```

## Recetas.

### Especifiar todo el bloque de comentarios sobre la clase, métodos y/o propiedades.

Para ello se debe emplear el método `setDocComment()`. El siguiente ejemplo muestra como especificar TODO el bloque de comentarios sobre una clase. En el caso de las propiedades y métodos se debe invocar sobre la respectiva definición.

```php
(new ClassBuilder('MyClass'))
    ->setDocComment("
        /**
         * Full doc block comments
         *
         * @Annotation1
         */
    ")
->install();
```

### Construir instancias directamente a través del builder.

```php
$andy = (new ClassBuilder('Person'))
    ->addMethod('__construct', function (string $name, int $age) {
        $this->name = $name;
        $this->age = $age;
    })->end()

    ->newInstance('andy', 31)
;
```

## Pendiente.

1. Poder especificar tipos en las propiedades. Ejemplo:

```php
(new ClassBuilder)
    ->addProperty('myProperty')
        ->setType('string', $nullable = true)
    ->end()
->install();
```

>Esta característica solo funcionaría a partir de PHP 7.4

2. Implementar otros *builders* para poder construir de forma similar *traits* e interfaces. Los nombres deberán ser `TraitBuilder` e `InterfaceBuilder` respectivamente.

3. (Optional) Implementar funcionalidad que permita ordenar el código PHP generador por un *builder*. Esto facilitaría la legibilidad del código cuando se esté depurando el mismo.
