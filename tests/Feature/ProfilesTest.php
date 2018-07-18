<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ProfilesTest extends TestCase
{
    use DatabaseMigrations;

    /*@test*/
    public function testUserHasProfile()
    {
        $user = create('App\User');

        $this->withoutExceptionHandling()->get("/profiles/{$user->name}")
            ->assertSee($user->name);
    }

    /*@test*/
    public function testProfilesDisplayAllThreadsOfThatUser()
    {
        $this->signIn();

        $thread = create('App\Thread', ['user_id' =>auth()->id()]);

        $this->withoutExceptionHandling()->get("/profiles/".auth()->user()->name)
            ->assertSee($thread->title)
            ->assertSee($thread->body);
    }
}
