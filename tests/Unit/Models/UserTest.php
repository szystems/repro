<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testUserModelCanBeInstantiated()
    {
        $user = new User();
        $this->assertInstanceOf(User::class, $user);
    }

    public function testUserHasCorrectTable()
    {
        $user = new User();
        $this->assertEquals('users', $user->getTable());
    }

    public function testUserHasFillableAttributes()
    {
        $user = new User();
        $this->assertContains('name', $user->getFillable());
        $this->assertContains('email', $user->getFillable());
    }
}