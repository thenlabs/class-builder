<?php

namespace ThenLabs\ClassBuilder\Tests;

use ThenLabs\ClassBuilder\InterfaceBuilder;

setTestCaseClass(TestCase::class);
setTestCaseNamespace(__NAMESPACE__);

testCase('InterfaceBuilderTest.php', function () {
    testCase('$builder = new InterfaceBuilder("MyTrait")', function () {
        setUp(function () {
            $this->builder = new InterfaceBuilder('MyInterface');
        });

        testCase('$builder->install()', function () {
            setUp(function () {
                $this->builder->install();
            });

            test('interface_exists("MyInterface") === true', function () {
                $this->assertTrue(interface_exists($this->builder->getFCQN()));
            });
        });
    });
});