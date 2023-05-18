<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    /** @test */
    public function it_returns_correct_initials()
    {
        $user = User::factory()->make([
            'name' => 'John Doe',
        ]);

        $initials = $user->getInitialsAttribute();

        $this->assertEquals('JD', $initials);

        //

        $user = User::factory()->make([
            'name' => 'John  Doe',
        ]);

        $initials = $user->getInitialsAttribute();

        $this->assertEquals('JD', $initials);
    }
}
