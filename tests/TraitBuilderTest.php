<?php

namespace ThenLabs\ClassBuilder\Tests;

use ThenLabs\ClassBuilder\TraitBuilder;

setTestCaseClass(TestCase::class);
setTestCaseNamespace(__NAMESPACE__);

testCase('TraitBuilderTest.php', function () {
    testCase('$builder = new TraitBuilder("MyTrait")', function () {
        setUp(function () {
            $this->builder = new TraitBuilder('MyTrait');
        });

        testCase('$builder->install()', function () {
            setUp(function () {
                $this->builder->install();
            });

            test('trait_exists("MyTrait") === true', function () {
                $this->assertTrue(trait_exists($this->builder->getFCQN()));
            });
        });
    });
});