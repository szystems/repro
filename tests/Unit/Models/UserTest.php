<?php

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCreation()
    {
        $user = new User();
        $user->setName('John Doe');
        $this->assertEquals('John Doe', $user->getName());
    }

    public function testUserEmailValidation()
    {
        $user = new User();
        $user->setEmail('invalid-email');
        $this->assertFalse($user->isValidEmail());

        $user->setEmail('john.doe@example.com');
        $this->assertTrue($user->isValidEmail());
    }
}