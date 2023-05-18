<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    /** @test */
    public function it_returns_correct_initials()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
        ]);

        $initials = $user->getInitialsAttribute();

        $this->assertEquals('JD', $initials);
    }
}
