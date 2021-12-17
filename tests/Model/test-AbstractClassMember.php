<?php

namespace ThenLabs\ClassBuilder\Tests\Model;

use ThenLabs\ClassBuilder\Exception\InvalidMemberNameException;
use ThenLabs\ClassBuilder\Model\AbstractClassMember;
use ThenLabs\ClassBuilder\Tests\TestCase;

setTestCaseClass(TestCase::class);

testCase('test-AbstractClassMember.php', function () {
    setUp(function () {
        $this->member = $this->getMockBuilder(AbstractClassMember::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass()
        ;
    });

    test(function () {
        $this->expectException(InvalidMemberNameException::class);

        $this->member->setName('abc dfc');
    });

    test(function () {
        $name = uniqid('member');

        $this->member->setName($name);

        $this->assertEquals($name, $this->member->getName());
    });
});
