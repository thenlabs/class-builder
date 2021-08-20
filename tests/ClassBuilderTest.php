<?php

namespace ThenLabs\ClassBuilder\Tests;

use ThenLabs\ClassBuilder\ClassBuilder;
use ThenLabs\ClassBuilder\Exception\InvalidClassNameException;
use ThenLabs\ClassBuilder\Exception\InvalidConstantNameException;
use ThenLabs\ClassBuilder\Exception\InvalidMethodNameException;
use ThenLabs\ClassBuilder\Exception\InvalidPropertyNameException;
use ThenLabs\ClassBuilder\Exception\InvalidNamespaceException;
use ThenLabs\ClassBuilder\Exception\InvalidAccessException;
use ThenLabs\ClassBuilder\Exception\InvalidEntityTypeException;
use ThenLabs\ClassBuilder\Exception\ExistentClassException;
use ThenLabs\ClassBuilder\Exception\UnexistentClassException;
use ThenLabs\ClassBuilder\Exception\UnexistentInterfaceException;
use ThenLabs\ClassBuilder\Exception\UnexistentTraitException;
use ThenLabs\ClassBuilder\Exception\UnsupportedFeatureException;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use Closure;

setTestCaseClass(TestCase::class);
setTestCaseNamespace(__NAMESPACE__);

define('VALUES', [
    uniqid(),
    '',
    mt_rand(0, 1000),
    0,
    floatval(mt_rand(0, 10).".".mt_rand(1, 10)),
    true,
    false,
    null,
    range(1, mt_rand(1, 10)),
]);

class DummyClass
{
}

trait DummyTrait1
{
    protected $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}

trait DummyTrait2
{
    public function getName(): string
    {
        return 'myName';
    }
}

testCase('ClassBuilderTest.php', function () {
    createMacro('returns the same builder', function () {
        test('returns the same builder', function () {
            $this->assertSame($this->result, $this->builder);
        });
    });

    createMacro('install and reflect the class', function (Closure $extendMacro = null) {
        testCase('$builder->install()', function () use ($extendMacro) {
            setUp(function () {
                $this->builder->install();
                $this->reflection = new ReflectionClass($this->builder->getFCQN());
            });

            test('the class has been created', function () {
                $this->assertInstanceOf(ReflectionClass::class, $this->reflection);
            });

            if ($extendMacro instanceof Closure) {
                $extendMacro();
            }
        });
    });

    createMethod('createTwoDoctrineExtensions', function () {
        $this->annotationNamespace = uniqid('Namespace');

        $this->annotationBuilder1 = new ClassBuilder(uniqid('Annotation'));
        $this->annotationBuilder1->addComment('@Annotation');
        $this->annotationBuilder1->setNamespace($this->annotationNamespace);
        $this->annotationBuilder1->install();

        $this->annotationBuilder2 = new ClassBuilder(uniqid('Annotation'));
        $this->annotationBuilder2->addComment('@Annotation');
        $this->annotationBuilder2->setNamespace($this->annotationNamespace);
        $this->annotationBuilder2->install();
    });

    test('toString() should return the FCQN', function () {
        $namespace = uniqid('Namespace');
        $name = uniqid('Class');

        $builder = new ClassBuilder($name);
        $builder->setNamespace($namespace);

        $this->assertEquals($builder->getFCQN(), (string) $builder);
    });

    testCase('isInstalled()', function () {
        test('returns false by default', function () {
            $builder = new ClassBuilder;

            $this->assertFalse($builder->isInstalled());
        });

        test('returns true after that the method install() is invoked', function () {
            $builder = new ClassBuilder;

            $builder->install();

            $this->assertTrue($builder->isInstalled());
        });
    });

    test('setName() is called in the constructor with the same argument', function () {
        $name = uniqid('MyClass');

        $builder = $this->getMockBuilder(ClassBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['setName'])
            ->getMock();
        $builder->expects($this->once())
            ->method('setName')
            ->with($this->equalTo($name))
        ;

        $builder->__construct($name);
    });

    test('when not sets a name in the constructor the default name prefix is "DynamicClass"', function () {
        $builder = $this->getMockBuilder(ClassBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['setName'])
            ->getMock();
        $builder->expects($this->once())
            ->method('setName')
            ->with($this->logicalAnd(
                $this->stringStartsWith('DynamicClass'),
                $this->callback(function ($value) {
                    return strlen($value) > 12 ? true : false;
                })
            ))
        ;

        $builder->__construct();
    });

    testCase('install() throwns an ExistentClassException', function () {
        setUp(function () {
            $this->expectException(ExistentClassException::class);
        });

        test(function () {
            $this->expectExceptionMessage("The class 'stdClass' already exists");

            (new ClassBuilder('stdClass'))->install();
        });

        test(function () {
            $builder = new ClassBuilder;

            $this->expectExceptionMessage("The class '{$builder->getName()}' already exists");

            $builder->install();
            $builder->install();
        });

        test(function () {
            $namespace = uniqid('namespace');
            $class = uniqid('Class');
            $fcqn = $namespace . '\\' . $class;

            eval("
                namespace {$namespace};

                class {$class} {}
            ");

            $this->expectExceptionMessage("The class '{$fcqn}' already exists");

            (new ClassBuilder($class))
                ->setNamespace($namespace)
                ->install()
            ;
        });
    });

    test('addInterface() throwns an UnexistentInterfaceException when it not exists', function () {
        $unexistentInterface = uniqid('Interface');
        $this->expectException(UnexistentInterfaceException::class);
        $this->expectExceptionMessage("The interface '{$unexistentInterface}' not exists.");

        (new ClassBuilder)->addInterface($unexistentInterface);
    });

    testCase('addConstant() throwns an InvalidConstantNameException', function () {
        $invalidConstantNames = [
            '01',
            'MY CONSTANT',
        ];

        foreach ($invalidConstantNames as $invalidConstantName) {
            test(function () use ($invalidConstantName) {
                $this->expectException(InvalidConstantNameException::class);
                $this->expectExceptionMessage("The constant name '{$invalidConstantName}' is invalid.");

                (new ClassBuilder)->addConstant($invalidConstantName);
            });
        }
    });

    testCase('addProperty() throwns an InvalidPropertyNameException', function () {
        $invalidPropertyNames = [
            '01',
            'MY CONSTANT',
        ];

        foreach ($invalidPropertyNames as $invalidPropertyName) {
            test(function () use ($invalidPropertyName) {
                $this->expectException(InvalidPropertyNameException::class);
                $this->expectExceptionMessage("The property name '{$invalidPropertyName}' is invalid.");

                (new ClassBuilder)->addProperty($invalidPropertyName);
            });
        }
    });

    testCase('addMethod() throwns an InvalidMethodNameException', function () {
        $invalidNames = [
            '01',
            'my method',
            12,
        ];

        foreach ($invalidNames as $invalidName) {
            test(function () use ($invalidName) {
                $this->expectException(InvalidMethodNameException::class);
                $this->expectExceptionMessage("The method name '{$invalidName}' is invalid.");

                (new ClassBuilder)->addMethod($invalidName);
            });
        }
    });

    testCase('$builder = new ClassBuilder($name)', function () {
        setUp(function () {
            $this->name = uniqid('MyClass');
            $this->builder = new ClassBuilder($this->name);
        });

        test('$builder->getName() === $name', function () {
            $this->assertEquals($this->name, $this->builder->getName());
        });

        test('$builder->getFCQN() === $name', function () {
            $this->assertEquals($this->name, $this->builder->getFCQN());
        });

        test('$builder->getNamespace() === null', function () {
            $this->assertNull($this->builder->getNamespace());
        });

        test('$builder->getInterfaces() === []', function () {
            $this->assertSame([], $this->builder->getInterfaces());
        });

        test('$builder->getEntityType() === "class"', function () {
            $this->assertSame(ClassBuilder::ENTITY_CLASS, $this->builder->getEntityType());
        });

        test('$builder->setEntityType(uniqid()) throwns an InvalidEntityTypeException', function () {
            $invalidEntityType = uniqid();

            $this->expectException(InvalidEntityTypeException::class);
            $this->expectExceptionMessage("The value '{$invalidEntityType}' is an invalid entity type. The valid values are 'class', 'trait' or 'interface'.");

            $this->builder->setEntityType($invalidEntityType);
        });

        test('$builder->extends("UnexistentClass") throwns an UnexistentClassException when the specified class not exists', function () {
            $unexistentClassName = uniqid('Class');

            $this->expectException(UnexistentClassException::class);
            $this->expectExceptionMessage("The class '{$unexistentClassName}' not exists.");

            $this->builder->extends($unexistentClassName);
        });

        test('$builder->getConstant(uniqid()) === null', function () {
            $this->assertNull($this->builder->getConstant(uniqid()));
        });

        testCase('$builder->implements("Interface1", "Interface2", ...) causes $builder->addInterface("Interface1"); $builder->addInterface("Interface2"); ...', function () {
            test(function () {
                $builder = $this->getMockBuilder(ClassBuilder::class)
                    ->disableOriginalConstructor()
                    ->setMethods(['addInterface'])
                    ->getMock();
                $builder->expects($this->exactly(3))
                    ->method('addInterface')
                    ->withConsecutive(
                        ['Interface1'],
                        ['Interface2'],
                        ['Interface3']
                    )
                ;

                $builder->implements('Interface1', 'Interface2', 'Interface3');
            });

            test(function () {
                $builder = $this->getMockBuilder(ClassBuilder::class)
                    ->disableOriginalConstructor()
                    ->setMethods(['addInterface'])
                    ->getMock();
                $builder->expects($this->exactly(5))
                    ->method('addInterface')
                    ->withConsecutive(
                        ['Interface1'],
                        ['Interface2'],
                        ['Interface3'],
                        ['Interface4'],
                        ['Interface5']
                    )
                ;

                $builder->implements('Interface1', 'Interface2', 'Interface3', 'Interface4', 'Interface5');
            });
        });

        useMacro('install and reflect the class');

        testCase('cases when setName() throwns an InvalidClassNameException', function () {
            setUp(function () {
                $this->expectException(InvalidClassNameException::class);
            });

            $invalidNames = ['My Class', 'My$Class', 123, '12Class'];
            foreach ($invalidNames as $invalidName) {
                test("the name '{$invalidName}' throwns an InvalidClassNameException", function () use ($invalidName) {
                    $this->expectExceptionMessage("The class name '{$invalidName}' is invalid.");

                    $this->builder->setName($invalidName);
                });
            }
        });

        testCase('$builder->setName($name2)', function () {
            setUp(function () {
                $this->name2 = uniqid('MyClass2');
                $this->result = $this->builder->setName($this->name2);
            });

            test('$builder->getName() === $name2', function () {
                $this->assertEquals($this->name2, $this->builder->getName());
            });

            useMacro('returns the same builder');

            useMacro('install and reflect the class');
        });

        testCase('cases when setNamespace() throwns an InvalidNamespaceException', function () {
            setUp(function () {
                $this->expectException(InvalidNamespaceException::class);
            });

            $invalidNamespaces = [
                'My\\Name space',
                'My\\45',
                'My\\Name\S pace',
                'My\\23\45',
            ];

            foreach ($invalidNamespaces as $invalidNamespace) {
                test("the value '{$invalidNamespace}' throwns an InvalidNamespaceException", function () use ($invalidNamespace) {
                    $this->expectExceptionMessage("The namespace '{$invalidNamespace}' is invalid.");

                    $this->builder->setNamespace($invalidNamespace);
                });
            }
        });

        testCase('$builder->setNamespace($namespace)', function () {
            setUp(function () {
                $this->namespace = uniqid('My') . '\\' . uniqid('CustomNamepace');
                $this->result = $this->builder->setNamespace($this->namespace);
            });

            useMacro('returns the same builder');

            test('$builder->getNamespace() === $namespace', function () {
                $this->assertEquals($this->namespace, $this->builder->getNamespace());
            });

            test('getFCQN() === "{$namespace}\\{$name}"', function () {
                $this->assertEquals(
                    $this->namespace . '\\' . $this->name,
                    $this->builder->getFCQN()
                );
            });

            useMacro('install and reflect the class');
        });

        testCase('$builder->extends($parentClass)', function () {
            setUp(function () {
                $this->parentClassNamespace = uniqid('namespace');
                $this->parentClassName = uniqid('Class');

                eval("
                    namespace {$this->parentClassNamespace};

                    class {$this->parentClassName} {}
                ");

                $this->parentClassFCQN = "{$this->parentClassNamespace}\\{$this->parentClassName}";

                $this->result = $this->builder->extends($this->parentClassFCQN);
            });

            useMacro('install and reflect the class', function () {
                test('the class extends the parent class', function () {
                    $this->assertEquals(
                        $this->parentClassFCQN,
                        $this->reflection->getParentClass()->getName()
                    );
                });
            });
        });

        testCase('exists an interface', function () {
            setUp(function () {
                $this->interfaceNamespace = uniqid('namespace');
                $this->interfaceName = uniqid('Interface');

                eval("
                    namespace {$this->interfaceNamespace};

                    interface {$this->interfaceName} {}
                ");

                $this->interfaceFCQN = "{$this->interfaceNamespace}\\{$this->interfaceName}";
            });

            testCase('$builder->addInterface($interfaceName)', function () {
                setUp(function () {
                    $this->result = $this->builder->addInterface($this->interfaceFCQN);
                });

                useMacro('returns the same builder');

                useMacro('install and reflect the class', function () {
                    test('the class implements the interface', function () {
                        $this->assertTrue($this->reflection->implementsInterface($this->interfaceFCQN));
                    });
                });
            });

            testCase('$builder->implements($interfaceName)', function () {
                setUp(function () {
                    $this->result = $this->builder->implements($this->interfaceFCQN);
                });

                useMacro('returns the same builder');
            });
        });

        createMacro('$builder->setDocComment($comments)', function () {
            testCase('$builder->setDocComment($comments)', function () {
                setUp(function () {
                    $word = uniqid('word');
                    $this->comments = trim("
                        /**
                         * {$word}
                         */
                    ");

                    $this->result = $this->builder->setDocComment($this->comments);
                });

                useMacro('returns the same builder');

                useMacro('install and reflect the class', function () {
                    test('the class has the defined comments', function () {
                        $this->assertEquals($this->comments, $this->reflection->getDocComment());
                    });
                });
            });
        });

        useMacro('$builder->setDocComment($comments)');

        testCase('$builder->addComment($comment)', function () {
            setUp(function () {
                $this->comment = uniqid();

                $this->result = $this->builder->addComment($this->comment);
            });

            useMacro('returns the same builder');

            useMacro('install and reflect the class', function () {
                test('the class has the expected comments', function () {
                    $expectedComments = "/**\n * {$this->comment}\n */";

                    $this->assertEquals($expectedComments, $this->reflection->getDocComment());
                });
            });

            useMacro('$builder->setDocComment($comments)');
        });

        testCase('exists two doctrine annotations', function () {
            setUp(function () {
                $this->createTwoDoctrineExtensions();
            });

            testCase('$builder->addComments("@Namespace1\Annotation1", "@Namespace2\Annotation2")', function () {
                setUp(function () {
                    $this->builder->setNamespace('MyNamespace');

                    $this->result = $this->builder->addComments(
                        "@{$this->annotationBuilder1->getFCQN()}",
                        "@{$this->annotationBuilder2->getFCQN()}"
                    );
                });

                useMacro('returns the same builder');

                useMacro('install and reflect the class', function () {
                    testCase('read the annotations of the class', function () {
                        setUp(function () {
                            $reader = new AnnotationReader;
                            $this->annotations = $reader->getClassAnnotations($this->reflection);
                        });

                        test('the class contains both annotations', function () {
                            $this->assertCount(2, $this->annotations);
                            $this->assertInstanceOf($this->annotationBuilder1->getFCQN(), $this->annotations[0]);
                            $this->assertInstanceOf($this->annotationBuilder2->getFCQN(), $this->annotations[1]);
                        });
                    });
                });

                useMacro('$builder->setDocComment($comments)');
            });
        });

        testCase('$builder->setFinal(true)', function () {
            setUp(function () {
                $this->result = $this->builder->setFinal(true);
            });

            useMacro('returns the same builder');

            useMacro('install and reflect the class', function () {
                test('the built class is final', function () {
                    $this->assertTrue($this->reflection->isFinal());
                });
            });
        });

        testCase('$builder->setAbstract(true)', function () {
            setUp(function () {
                $this->result = $this->builder->setAbstract(true);
            });

            useMacro('returns the same builder');

            useMacro('install and reflect the class', function () {
                test('the built class is abstract', function () {
                    $this->assertTrue($this->reflection->isAbstract());
                });
            });

            testCase('$builder->addMethod("myMethod")->setAbstract(true)', function () {
                setUp(function () {
                    $this->methodName = uniqid('method');
                    $this->methodBuilder = $this->builder->addMethod($this->methodName);
                });

                testCase('$methodBuilder->setAbstract(true)', function () {
                    setUp(function () {
                        $this->methodBuilder->setAbstract(true);
                    });

                    useMacro('install and reflect the class', function () {
                        test('the method is abstract', function () {
                            $reflectionMethod = $this->reflection->getMethod($this->methodName);

                            $this->assertTrue($reflectionMethod->isAbstract());
                        });
                    });
                });
            });
        });

        testCase('$builder->addProperty("propertyName")', function () {
            setUp(function () {
                $this->propertyName = uniqid('property');
                $this->property = $this->builder->addProperty($this->propertyName);
            });

            createMacro('ends, install and reflect the class', function ($tests) {
                testCase('->end()', function () use ($tests) {
                    setUp(function () {
                        $this->result = $this->property->end();
                    });

                    useMacro('returns the same builder');

                    useMacro('install and reflect the class', function () use ($tests) {
                        $tests();
                    });
                });
            });

            createMacro('returns the same property builder', function () {
                test('returns the same property builder', function () {
                    $this->assertSame($this->property, $this->result);
                });
            });

            useMacro('ends, install and reflect the class', function () {
                test('the class has the defined public property', function () {
                    $property = $this->reflection->getProperty($this->propertyName);

                    $this->assertTrue($property->isPublic());
                });
            });

            test('$property->setAccess("invalidAccess") throwns an InvalidAccessException', function () {
                $invalidAccess = uniqid('access');
                $this->expectException(InvalidAccessException::class);
                $this->expectExceptionMessage("The access '{$invalidAccess}' is invalid. The accepted values they are 'public', 'private' or 'protected'.");

                $this->property->setAccess($invalidAccess);
            });

            testCase('$property->setAccess("public")', function () {
                setUp(function () {
                    $this->result = $this->property->setAccess('public');
                });

                useMacro('returns the same property builder');

                useMacro('ends, install and reflect the class', function () {
                    test('the class has the defined public property', function () {
                        $property = $this->reflection->getProperty($this->propertyName);

                        $this->assertTrue($property->isPublic());
                    });
                });
            });

            testCase('$property->setAccess("private")', function () {
                setUp(function () {
                    $this->result = $this->property->setAccess('private');
                });

                useMacro('returns the same property builder');

                useMacro('ends, install and reflect the class', function () {
                    test('the class has the defined private property', function () {
                        $property = $this->reflection->getProperty($this->propertyName);

                        $this->assertTrue($property->isPrivate());
                    });
                });
            });

            testCase('$property->setAccess("protected")', function () {
                setUp(function () {
                    $this->result = $this->property->setAccess('protected');
                });

                useMacro('returns the same property builder');

                useMacro('ends, install and reflect the class', function () {
                    test('the class has the defined protected property', function () {
                        $property = $this->reflection->getProperty($this->propertyName);

                        $this->assertTrue($property->isProtected());
                    });
                });
            });

            testCase('$property->setStatic(true)', function () {
                setUp(function () {
                    $this->result = $this->property->setStatic(true);
                });

                useMacro('returns the same property builder');

                useMacro('ends, install and reflect the class', function () {
                    test('the defined property is static', function () {
                        $property = $this->reflection->getProperty($this->propertyName);

                        $this->assertTrue($property->isStatic());
                    });
                });
            });

            if (version_compare(PHP_VERSION, '7.4.0', '<')) {
                test('$property->setType() throwns UnsupportedFeatureException', function () {
                    $this->expectException(UnsupportedFeatureException::class);

                    $this->property->setType('string');
                });
            }

            if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
                testCase('$property->setType("string")', function () {
                    setUp(function () {
                        $this->result = $this->property->setType('string');
                    });

                    useMacro('returns the same property builder');

                    useMacro('ends, install and reflect the class', function () {
                        test('the defined property type is string', function () {
                            $property = $this->reflection->getProperty($this->propertyName);
                            $propertyType = $property->getType();

                            $this->assertEquals('string', $propertyType->getName());
                            $this->assertFalse($propertyType->allowsNull());
                        });
                    });
                });

                testCase('$property->setType("?int")', function () {
                    setUp(function () {
                        $this->property->setValue(0);
                        $this->result = $this->property->setType('?int');
                    });

                    useMacro('returns the same property builder');

                    useMacro('ends, install and reflect the class', function () {
                        test('the defined property type is ?int', function () {
                            $property = $this->reflection->getProperty($this->propertyName);
                            $propertyType = $property->getType();

                            $this->assertEquals('int', $propertyType->getName());
                            $this->assertTrue($propertyType->allowsNull());
                        });
                    });
                });

                testCase('$property->setType("?\ThenLabs\ClassBuilder\Tests\DummyClass")', function () {
                    setUp(function () {
                        $this->result = $this->property->setType("?\ThenLabs\ClassBuilder\Tests\DummyClass");
                    });

                    useMacro('returns the same property builder');

                    useMacro('ends, install and reflect the class', function () {
                        test('the defined property type is ?\ThenLabs\ClassBuilder\Tests\DummyClass', function () {
                            $property = $this->reflection->getProperty($this->propertyName);
                            $propertyType = $property->getType();

                            $this->assertEquals(DummyClass::class, $propertyType->getName());
                            $this->assertTrue($propertyType->allowsNull());
                        });
                    });
                });
            }

            foreach (VALUES as $value) {
                $description = var_export($value, true);
                testCase("\$property->setDefaultValue({$description})", function () use ($value) {
                    setUp(function () use ($value) {
                        $this->result = $this->property->setDefaultValue($value);
                    });

                    useMacro('returns the same property builder');

                    useMacro('ends, install and reflect the class', function () use ($value) {
                        test('the defined property has the expected default value', function () use ($value) {
                            $property = $this->reflection->getProperty($this->propertyName);

                            $fcqn = $this->builder->getFCQN();
                            $instance = new $fcqn;

                            $this->assertTrue($property->isDefault());
                            $this->assertSame($value, $property->getValue($instance));
                        });
                    });
                });
            }

            createMacro('$property->setDocComment($comments)', function () {
                testCase('$property->setDocComment($comments)', function () {
                    setUp(function () {
                        $word = uniqid('word');
                        $this->comments = trim("
                            /**
                             * {$word}
                             */
                        ");

                        $this->result = $this->property->setDocComment($this->comments);
                    });

                    useMacro('returns the same property builder');

                    useMacro('install and reflect the class', function () {
                        test('the property has the expected comments', function () {
                            $property = $this->reflection->getProperty($this->propertyName);

                            $this->assertEquals($this->comments, $property->getDocComment());
                        });
                    });
                });
            });

            useMacro('$property->setDocComment($comments)');

            testCase('$property->addComment($comment)', function () {
                setUp(function () {
                    $this->comment = uniqid();

                    $this->result = $this->property->addComment($this->comment);
                });

                useMacro('returns the same property builder');

                useMacro('install and reflect the class', function () {
                    test('the property has the expected comment', function () {
                        $expectedComments = "/**\n * {$this->comment}\n */";
                        $property = $this->reflection->getProperty($this->propertyName);

                        $this->assertEquals($expectedComments, $property->getDocComment());
                    });
                });

                useMacro('$property->setDocComment($comments)');
            });

            testCase('$property->addComments("@Namespace1\Annotation1", "@Namespace2\Annotation2")', function () {
                setUp(function () {
                    $this->createTwoDoctrineExtensions();

                    $this->result = $this->property->addComments(
                        "@{$this->annotationBuilder1->getFCQN()}",
                        "@{$this->annotationBuilder2->getFCQN()}"
                    );
                });

                useMacro('returns the same property builder');

                useMacro('install and reflect the class', function () {
                    testCase('read the annotations of the property', function () {
                        setUp(function () {
                            $reader = new AnnotationReader;
                            $property = $this->reflection->getProperty($this->propertyName);

                            $this->annotations = $reader->getPropertyAnnotations($property);
                        });

                        test('the property contains both annotations', function () {
                            $this->assertCount(2, $this->annotations);
                            $this->assertInstanceOf($this->annotationBuilder1->getFCQN(), $this->annotations[0]);
                            $this->assertInstanceOf($this->annotationBuilder2->getFCQN(), $this->annotations[1]);
                        });
                    });
                });

                useMacro('$property->setDocComment($comments)');
            });
        });

        testCase('$builder->addConstant($constantName)', function () {
            setUp(function () {
                $this->constantName = uniqid('CONSTANT_');

                $this->constant = $this->builder->addConstant($this->constantName);
            });

            createMacro('ends, install and reflect the class', function ($tests = null) {
                testCase('->end()', function () use ($tests) {
                    setUp(function () {
                        $this->result = $this->constant->end();
                    });

                    useMacro('returns the same builder');

                    useMacro('install and reflect the class', function () use ($tests) {
                        if ($tests instanceof Closure) {
                            $tests();
                        }
                    });
                });
            });

            useMacro('ends, install and reflect the class', function () {
                test('the class has the defined public constant', function () {
                    $this->assertTrue($this->reflection->hasConstant($this->constantName));
                });

                testCase('the constant', function () {
                    setUp(function () {
                        $this->reflectionConstant = $this->reflection->getReflectionConstant($this->constantName);
                    });

                    test('is public', function () {
                        $this->assertTrue($this->reflectionConstant->isPublic());
                    });

                    test('is null', function () {
                        $this->assertNull($this->reflectionConstant->getValue());
                    });
                });
            });

            foreach (VALUES as $value) {
                $description = var_export($value, true);
                testCase("\$constant->setValue({$description})", function () use ($value) {
                    setUp(function () use ($value) {
                        $this->constant->setValue($value);
                    });

                    useMacro('ends, install and reflect the class', function () use ($value) {
                        test('the class has the defined public constant', function () {
                            $this->assertTrue($this->reflection->hasConstant($this->constantName));
                        });

                        testCase('the constant', function () use ($value) {
                            setUp(function () {
                                $this->reflectionConstant = $this->reflection->getReflectionConstant($this->constantName);
                            });

                            test('is public', function () {
                                $this->assertTrue($this->reflectionConstant->isPublic());
                            });

                            test('has the expected value', function () use ($value) {
                                $this->assertEquals($value, $this->reflectionConstant->getValue());
                            });
                        });
                    });
                });
            }

            test('$builder->getConstant($constantName) returns the model when exists', function () {
                $this->assertSame(
                    $this->constant,
                    $this->builder->getConstant($this->constantName)
                );
            });

            test('$constant->setAccess("invalidAccess") throwns an InvalidAccessException', function () {
                $invalidAccess = uniqid('access');
                $this->expectException(InvalidAccessException::class);
                $this->expectExceptionMessage("The access '{$invalidAccess}' is invalid. The accepted values they are 'public', 'private' or 'protected'.");

                $this->constant->setAccess($invalidAccess);
            });

            createMacro('returns the same constant builder', function () {
                test('returns the same constant builder', function () {
                    $this->assertSame($this->constant, $this->result);
                });
            });

            createMacro('reflect the constant', function (Closure $tests) {
                useMacro('ends, install and reflect the class', function () use ($tests) {
                    test('the class has the defined constant', function () {
                        $this->assertTrue($this->reflection->hasConstant($this->constantName));
                    });

                    testCase('the constant', function () use ($tests) {
                        setUp(function () {
                            $this->reflectionConstant = $this->reflection->getReflectionConstant($this->constantName);
                        });

                        $tests();
                    });
                });
            });

            testCase('$constant->setAccess("public")', function () {
                setUp(function () {
                    $this->result = $this->constant->setAccess('public');
                });

                useMacro('returns the same constant builder');

                useMacro('reflect the constant', function () {
                    test('is public', function () {
                        $this->assertTrue($this->reflectionConstant->isPublic());
                    });
                });
            });

            testCase('$constant->setAccess("protected")', function () {
                setUp(function () {
                    $this->result = $this->constant->setAccess('protected');
                });

                useMacro('returns the same constant builder');

                useMacro('reflect the constant', function () {
                    test('is protected', function () {
                        $this->assertTrue($this->reflectionConstant->isPublic());
                    });
                });
            });

            testCase('$constant->setAccess("private")', function () {
                setUp(function () {
                    $this->result = $this->constant->setAccess('private');
                });

                useMacro('returns the same constant builder');

                useMacro('reflect the constant', function () {
                    test('is private', function () {
                        $this->assertTrue($this->reflectionConstant->isPublic());
                    });
                });
            });
        });

        testCase('$builder->addMethod($methodName)', function () {
            setUp(function () {
                $this->methodName = uniqid('method');

                $this->methodBuilder = $this->builder->addMethod($this->methodName);
            });

            createMacro('ends, install and reflect the class', function ($tests = null) {
                testCase('->end()', function () use ($tests) {
                    setUp(function () {
                        $this->result = $this->methodBuilder->end();
                    });

                    useMacro('returns the same builder');

                    useMacro('install and reflect the class', function () use ($tests) {
                        if ($tests instanceof Closure) {
                            $tests();
                        }
                    });
                });
            });

            useMacro('ends, install and reflect the class', function () {
                test('the class has the defined method', function () {
                    $this->assertTrue($this->reflection->hasMethod($this->methodName));
                });

                testCase('reflect the method', function () {
                    setUp(function () {
                        $this->reflectionMethod = $this->reflection->getMethod($this->methodName);
                    });

                    testCase('the method', function () {
                        test('is public', function () {
                            $this->assertTrue($this->reflectionMethod->isPublic());
                        });

                        test('is not abstract', function () {
                            $this->assertFalse($this->reflectionMethod->isAbstract());
                        });

                        test('is not final', function () {
                            $this->assertFalse($this->reflectionMethod->isFinal());
                        });

                        test('has not parameters', function () {
                            $this->assertEmpty($this->reflectionMethod->getNumberOfParameters());
                        });

                        test('has not return type', function () {
                            $this->assertNull($this->reflectionMethod->getReturnType());
                        });
                    });
                });
            });

            test('$methodBuilder->setAccess("invalidAccess") throwns an InvalidAccessException', function () {
                $invalidAccess = uniqid('access');
                $this->expectException(InvalidAccessException::class);
                $this->expectExceptionMessage("The access '{$invalidAccess}' is invalid. The accepted values they are 'public', 'private' or 'protected'.");

                $this->methodBuilder->setAccess($invalidAccess);
            });

            createMacro('returns the same method builder', function () {
                test('returns the same method builder', function () {
                    $this->assertSame($this->methodBuilder, $this->result);
                });
            });

            testCase('$methodBuilder->setAccess("public")', function () {
                setUp(function () {
                    $this->result = $this->methodBuilder->setAccess('public');
                });

                useMacro('returns the same method builder');

                useMacro('ends, install and reflect the class', function () {
                    testCase('reflect the method', function () {
                        setUp(function () {
                            $this->reflectionMethod = $this->reflection->getMethod($this->methodName);
                        });

                        testCase('the method', function () {
                            test('is public', function () {
                                $this->assertTrue($this->reflectionMethod->isPublic());
                            });
                        });
                    });
                });
            });

            testCase('$methodBuilder->setAccess("protected")', function () {
                setUp(function () {
                    $this->result = $this->methodBuilder->setAccess('protected');
                });

                useMacro('returns the same method builder');

                useMacro('ends, install and reflect the class', function () {
                    testCase('reflect the method', function () {
                        setUp(function () {
                            $this->reflectionMethod = $this->reflection->getMethod($this->methodName);
                        });

                        testCase('the method', function () {
                            test('is protected', function () {
                                $this->assertTrue($this->reflectionMethod->isProtected());
                            });
                        });
                    });
                });
            });

            testCase('$methodBuilder->setAccess("private")', function () {
                setUp(function () {
                    $this->result = $this->methodBuilder->setAccess('private');
                });

                useMacro('returns the same method builder');

                useMacro('ends, install and reflect the class', function () {
                    testCase('reflect the method', function () {
                        setUp(function () {
                            $this->reflectionMethod = $this->reflection->getMethod($this->methodName);
                        });

                        testCase('the method', function () {
                            test('is private', function () {
                                $this->assertTrue($this->reflectionMethod->isPrivate());
                            });
                        });
                    });
                });
            });

            testCase('$methodBuilder->setStatic(true)', function () {
                setUp(function () {
                    $this->result = $this->methodBuilder->setStatic(true);
                });

                useMacro('returns the same method builder');

                useMacro('ends, install and reflect the class', function () {
                    testCase('reflect the method', function () {
                        setUp(function () {
                            $this->reflectionMethod = $this->reflection->getMethod($this->methodName);
                        });

                        testCase('the method', function () {
                            test('is static', function () {
                                $this->assertTrue($this->reflectionMethod->isStatic());
                            });
                        });
                    });
                });
            });

            createMacro('$methodBuilder->setDocComment($comments)', function () {
                testCase('$methodBuilder->setDocComment($comments)', function () {
                    setUp(function () {
                        $word = uniqid('word');
                        $this->comments = trim("
                            /**
                             * {$word}
                             */
                        ");

                        $this->result = $this->methodBuilder->setDocComment($this->comments);
                    });

                    useMacro('returns the same method builder');

                    useMacro('install and reflect the class', function () {
                        test('the method has the expected comments', function () {
                            $method = $this->reflection->getMethod($this->methodName);

                            $this->assertEquals($this->comments, $method->getDocComment());
                        });
                    });
                });
            });

            useMacro('$methodBuilder->setDocComment($comments)');

            testCase('$methodBuilder->addComment($comment)', function () {
                setUp(function () {
                    $this->comment = uniqid();

                    $this->result = $this->methodBuilder->addComment($this->comment);
                });

                useMacro('returns the same method builder');

                useMacro('install and reflect the class', function () {
                    test('the method has the expected comment', function () {
                        $expectedComments = "/**\n * {$this->comment}\n */";
                        $method = $this->reflection->getMethod($this->methodName);

                        $this->assertEquals($expectedComments, $method->getDocComment());
                    });
                });

                useMacro('$methodBuilder->setDocComment($comments)');
            });

            testCase('$methodBuilder->addComments("@Namespace1\Annotation1", "@Namespace2\Annotation2")', function () {
                setUp(function () {
                    $this->createTwoDoctrineExtensions();

                    $this->result = $this->methodBuilder->addComments(
                        "@{$this->annotationBuilder1->getFCQN()}",
                        "@{$this->annotationBuilder2->getFCQN()}"
                    );
                });

                useMacro('returns the same method builder');

                useMacro('install and reflect the class', function () {
                    testCase('read the annotations of the property', function () {
                        setUp(function () {
                            $reader = new AnnotationReader;
                            $method = $this->reflection->getMethod($this->methodName);

                            $this->annotations = $reader->getMethodAnnotations($method);
                        });

                        test('the property contains both annotations', function () {
                            $this->assertCount(2, $this->annotations);
                            $this->assertInstanceOf($this->annotationBuilder1->getFCQN(), $this->annotations[0]);
                            $this->assertInstanceOf($this->annotationBuilder2->getFCQN(), $this->annotations[1]);
                        });
                    });
                });

                useMacro('$methodBuilder->setDocComment($comments)');
            });

            testCase('$methodBuilder->setClosure()', function () {
                setUp(function () {
                    $this->builder->setNamespace('MyNamespace');
                });

                createMethod('installAndReflectTheMethod', function () {
                    $this->builder->install();
                    $this->reflection = new ReflectionClass($this->builder->getFCQN());
                    $this->reflectionMethod = $this->reflection->getMethod($this->methodName);
                });

                test('return the same method builder', function () {
                    $this->assertSame($this->methodBuilder, $this->methodBuilder->setClosure(function () {
                    }));
                });

                testCase('the return type of the method is equal to the return type of closure', function () {
                    test('when the closure has not return type', function () {
                        $this->methodBuilder->setClosure(function () {
                        });

                        $this->installAndReflectTheMethod();

                        $this->assertNull($this->reflectionMethod->getReturnType());
                    });

                    test('when the return type is "void"', function () {
                        $this->methodBuilder->setClosure(function (): void {
                        });

                        $this->installAndReflectTheMethod();

                        $type = $this->reflectionMethod->getReturnType();
                        $this->assertEquals('void', $type->getName());
                        $this->assertFalse($type->allowsNull());
                    });

                    test('when the return type is "string"', function () {
                        $this->methodBuilder->setClosure(function (): string {
                        });

                        $this->installAndReflectTheMethod();

                        $type = $this->reflectionMethod->getReturnType();
                        $this->assertEquals('string', $type->getName());
                        $this->assertFalse($type->allowsNull());
                    });

                    test('when the return type is "?float"', function () {
                        $this->methodBuilder->setClosure(function (): ?float {
                        });

                        $this->installAndReflectTheMethod();

                        $type = $this->reflectionMethod->getReturnType();
                        $this->assertEquals('float', $type->getName());
                        $this->assertTrue($type->allowsNull());
                    });

                    test('when the return type is "?array"', function () {
                        $this->methodBuilder->setClosure(function (): ?array {
                        });

                        $this->installAndReflectTheMethod();

                        $type = $this->reflectionMethod->getReturnType();
                        $this->assertEquals('array', $type->getName());
                        $this->assertTrue($type->allowsNull());
                    });

                    test('when the return type is "?object"', function () {
                        $this->methodBuilder->setClosure(function (): ?object {
                        });

                        $this->installAndReflectTheMethod();

                        $type = $this->reflectionMethod->getReturnType();
                        $this->assertEquals('object', $type->getName());
                        $this->assertTrue($type->allowsNull());
                    });

                    test('when the return type is "?iterable"', function () {
                        $this->methodBuilder->setClosure(function (): ?iterable {
                        });

                        $this->installAndReflectTheMethod();

                        $type = $this->reflectionMethod->getReturnType();
                        $this->assertEquals('iterable', $type->getName());
                        $this->assertTrue($type->allowsNull());
                    });

                    test('when the return type is "?self"', function () {
                        $this->methodBuilder->setClosure(function (): ?self {
                        });

                        $this->installAndReflectTheMethod();

                        $type = $this->reflectionMethod->getReturnType();
                        $this->assertEquals('self', $type->getName());
                        $this->assertTrue($type->allowsNull());
                    });

                    test('when the return type is "?\stdClass"', function () {
                        $this->methodBuilder->setClosure(function (): ?\stdClass {
                        });

                        $this->installAndReflectTheMethod();

                        $type = $this->reflectionMethod->getReturnType();
                        $this->assertEquals('stdClass', $type->getName());
                        $this->assertTrue($type->allowsNull());
                    });

                    test('when the return type is "?callable"', function () {
                        $this->methodBuilder->setClosure(function (): ?callable {
                        });

                        $this->installAndReflectTheMethod();

                        $type = $this->reflectionMethod->getReturnType();
                        $this->assertEquals('callable', $type->getName());
                        $this->assertTrue($type->allowsNull());
                    });

                    testCase('the return type may be sets by setReturnType()', function () {
                        test('$methodBuilder->setReturnType("?string")', function () {
                            $this->methodBuilder->setClosure(function (): ?callable {
                            });

                            $this->methodBuilder->setReturnType('?string');

                            $this->assertSame('?string', $this->methodBuilder->getReturnType());

                            $this->installAndReflectTheMethod();

                            $type = $this->reflectionMethod->getReturnType();
                            $this->assertEquals('string', $type->getName());
                            $this->assertTrue($type->allowsNull());
                        });

                        test('$methodBuilder->setReturnType("void")', function () {
                            $this->methodBuilder->setClosure(function (): ?callable {
                            });

                            $this->methodBuilder->setReturnType('void');

                            $this->assertSame('void', $this->methodBuilder->getReturnType());

                            $this->installAndReflectTheMethod();

                            $type = $this->reflectionMethod->getReturnType();
                            $this->assertEquals('void', $type->getName());
                            $this->assertFalse($type->allowsNull());
                        });
                    });
                });

                testCase('the method has the same parameters that the closure', function () {
                    test('when the closure has not parameters', function () {
                        $this->methodBuilder->setClosure(function () {
                        });

                        $this->installAndReflectTheMethod();

                        $this->assertEmpty($this->reflectionMethod->getParameters());
                    });

                    test('when the closure several parameters', function () {
                        $this->methodBuilder->setClosure(
                            function (
                                &$arg1,
                                ?string $arg2 = 'abc',
                                array $arg3 = [],
                                DummyClass $arg4 = null
                            ) {
                            }
                        );

                        $this->installAndReflectTheMethod();

                        $parameters = $this->reflectionMethod->getParameters();
                        $this->assertCount(4, $parameters);

                        $param1 = $parameters[0];
                        $this->assertEquals('arg1', $param1->getName());
                        $this->assertFalse($param1->isOptional());
                        $this->assertFalse($param1->hasType());
                        $this->assertTrue($param1->allowsNull());
                        $this->assertTrue($param1->isPassedByReference());

                        $param2 = $parameters[1];
                        $type2 = $param2->getType();
                        $this->assertEquals('arg2', $param2->getName());
                        $this->assertTrue($param2->isOptional());
                        $this->assertTrue($param2->hasType());
                        $this->assertEquals('string', $type2->getName());
                        $this->assertTrue($param2->allowsNull());
                        $this->assertFalse($param2->isPassedByReference());

                        $param3 = $parameters[2];
                        $type3 = $param3->getType();
                        $this->assertEquals('arg3', $param3->getName());
                        $this->assertTrue($param3->isOptional());
                        $this->assertTrue($param3->hasType());
                        $this->assertTrue($param3->isArray());
                        $this->assertEquals('array', $type3->getName());
                        $this->assertFalse($param3->allowsNull());
                        $this->assertFalse($param3->isPassedByReference());

                        $param4 = $parameters[3];
                        $type3 = $param4->getType();
                        $this->assertEquals('arg4', $param4->getName());
                        $this->assertTrue($param4->isOptional());
                        $this->assertTrue($param4->hasType());
                        $this->assertEquals(DummyClass::class, $type3->getName());
                        $this->assertTrue($param4->allowsNull());
                        $this->assertFalse($param4->isPassedByReference());
                    });

                    test('when the closure has a variadic parameter', function () {
                        $this->methodBuilder->setClosure(
                            function (?string ...$arg) {
                            }
                        );

                        $this->installAndReflectTheMethod();

                        $parameters = $this->reflectionMethod->getParameters();
                        $this->assertCount(1, $parameters);

                        $param1 = $parameters[0];
                        $this->assertEquals('arg', $param1->getName());
                        $this->assertTrue($param1->isOptional());
                        $this->assertTrue($param1->hasType());
                        $this->assertTrue($param1->allowsNull());
                        $this->assertTrue($param1->isVariadic());
                    });
                });

                test('testing method invocation with return type', function () {
                    $this->builder
                        ->addMethod('sayHello')
                            ->setClosure(function (string $name): string {
                                return 'hi ' . $name;
                            })
                        ->end()
                    ->install();

                    $class = $this->builder->getFCQN();
                    $instance = new $class;
                    $name = uniqid('name');

                    $this->assertEquals("hi {$name}", $instance->sayHello($name));
                });

                test('testing manual property creation with getter and setter', function () {
                    $this->builder
                        ->addProperty('name')
                            ->setAccess('protected')
                        ->end()

                        ->addMethod('getName', function (): string {
                            return $this->name;
                        })
                        ->end()

                        ->addMethod('setName', function (string $name): void {
                            $this->name = $name;
                        })
                        ->end()
                    ->install();

                    $class = $this->builder->getFCQN();
                    $instance = new $class;
                    $name = uniqid('name');

                    $instance->setName($name);

                    $this->assertEquals($name, $instance->getName());
                });

                test('testing method invocation without return type', function () {
                    $this->builder
                        ->addMethod('sayHello')
                            ->setClosure(function (string $name) {
                                return 'hi ' . $name;
                            })
                        ->end()
                    ->install();

                    $class = $this->builder->getFCQN();
                    $instance = new $class;
                    $name = uniqid('name');

                    $this->assertEquals("hi {$name}", $instance->sayHello($name));
                });

                test('testing static method invocation with return type', function () {
                    $this->builder
                        ->addMethod('sayHello')
                            ->setStatic(true)
                            ->setClosure(function (string $name): string {
                                return 'hi ' . $name;
                            })
                        ->end()
                    ->install();

                    $class = $this->builder->getFCQN();
                    $name = uniqid('name');

                    $this->assertEquals("hi {$name}", $class::sayHello($name));
                });

                test('testing static methods that refers to self::class', function () {
                    $this->builder
                        ->addMethod('sayHello')
                            ->setStatic(true)
                            ->setClosure(function (string $name): string {
                                return 'hi ' . $name;
                            })
                        ->end()

                        ->addMethod('hi')
                            ->setClosure(function (string $name): string {
                                return self::sayHello($name);
                            })
                        ->end()
                    ->install();

                    $class = $this->builder->getFCQN();
                    $instance = new $class;

                    $name = uniqid('name');

                    $this->assertEquals("hi {$name}", $instance->hi($name));
                });

                test('testing static methods that refers to static::class', function () {
                    $this->builder
                        ->addMethod('sayHello')
                            ->setStatic(true)
                            ->setClosure(function (string $name): string {
                                return 'hi ' . $name;
                            })
                        ->end()

                        ->addMethod('hi')
                            ->setClosure(function (string $name): string {
                                return static::sayHello($name);
                            })
                        ->end()
                    ->install();

                    $class = $this->builder->getFCQN();
                    $instance = new $class;

                    $name = uniqid('name');

                    $this->assertEquals("hi {$name}", $instance->hi($name));
                });
            });
        });

        test('$builder->use(Trait1::class) throwns UnexistentTraitException when the trait not exists', function () {
            $unexistentTrait = uniqid('Trait');
            $this->expectException(UnexistentTraitException::class);
            $this->expectExceptionMessage("The trait '{$unexistentTrait}' not exists.");

            (new ClassBuilder)->use($unexistentTrait);
        });

        testCase('$builder->use(Trait1::class)', function () {
            setUp(function () {
                $this->result = $this->builder->use(DummyTrait1::class);
            });

            useMacro('returns the same builder');

            useMacro('install and reflect the class', function () {
                test('the class use the trait', function () {
                    $class = $this->builder->getFCQN();
                    $instance = new $class;

                    $name = uniqid();
                    $instance->setName($name);

                    $this->assertSame($name, $instance->getName());
                });
            });
        });

        testCase('$builder->use(Trait1::class, ["func1 as alias1", ...])', function () {
            setUp(function () {
                $this->result = $this->builder->use(DummyTrait1::class, ['getName as _getName']);
            });

            useMacro('returns the same builder');

            useMacro('install and reflect the class', function () {
                test('the class use the trait', function () {
                    $class = $this->builder->getFCQN();
                    $instance = new $class;

                    $name = uniqid();
                    $instance->setName($name);

                    $this->assertSame($name, $instance->_getName());
                });
            });
        });

        testCase('$builder->use([Trait1::class, Trait2::class], ["Trait2::func1 insteadof Trait1", ...])', function () {
            setUp(function () {
                $traitClass1 = DummyTrait1::class;
                $traitClass2 = DummyTrait2::class;

                $this->result = $this->builder->use(
                    [$traitClass2, $traitClass1],
                    ["{$traitClass1}::getName insteadof {$traitClass2}"]
                );
            });

            useMacro('returns the same builder');

            useMacro('install and reflect the class', function () {
                test('the class use the trait', function () {
                    $class = $this->builder->getFCQN();
                    $instance = new $class;

                    $name = uniqid();
                    $instance->setName($name);

                    $this->assertSame($name, $instance->getName());
                });
            });
        });

        testCase('$builder->setEntityType(ClassBuilder::ENTITY_TRAIT)', function () {
            setUp(function () {
                $this->result = $this->builder->setEntityType(ClassBuilder::ENTITY_TRAIT);
            });

            useMacro('returns the same builder');

            useMacro('install and reflect the class', function () {
                test('the trait has been created', function () {
                    $this->assertTrue(trait_exists($this->builder->getFCQN()));
                });
            });
        });

        testCase('$builder->setEntityType(ClassBuilder::ENTITY_INTERFACE)', function () {
            setUp(function () {
                $this->result = $this->builder->setEntityType(ClassBuilder::ENTITY_INTERFACE);
            });

            useMacro('returns the same builder');

            useMacro('install and reflect the class', function () {
                test('the trait has been created', function () {
                    $this->assertTrue(interface_exists($this->builder->getFCQN()));
                });
            });

            test(function () {
                $this->builder
                    ->addConstant('MY_CONSTANT')
                        ->setAccess('private')
                        ->setValue('myValue')
                    ->end()
                    ->addProperty('myProperty')
                    ->end()
                    ->addMethod('myMethod', function (int $a, ?float $b, $c = null): string {
                        return '';
                    })
                    ->end()
                ;

                $this->builder->install();

                $this->assertTrue(interface_exists($this->builder->getFCQN()));
            });
        });

        test('$builder->newInstance($arg1, $arg2)', function () {
            $builder = (new ClassBuilder)
                ->addProperty('name')->setAccess('protected')->end()
                ->addProperty('age')->setAccess('protected')->end()

                ->addMethod('__construct', function (string $name, int $age) {
                    $this->name = $name;
                    $this->age = $age;
                })->end()

                ->addMethod('getName', function () {
                    return $this->name;
                })->end()

                ->addMethod('getAge', function () {
                    return $this->age;
                })->end()
            ;

            $name1 = uniqid();
            $age1 = mt_rand(20, 50);
            $person1 = $builder->newInstance($name1, $age1);

            $name2 = uniqid();
            $age2 = mt_rand(20, 50);
            $person2 = $builder->newInstance($name2, $age2);

            $this->assertEquals($name1, $person1->getName());
            $this->assertEquals($age1, $person1->getAge());

            $this->assertEquals($name2, $person2->getName());
            $this->assertEquals($age2, $person2->getAge());
        });

        test('$builder->install() returns the same builder', function () {
            $builder = new ClassBuilder;

            $this->assertSame($builder, $builder->install());
        });
    });
});
