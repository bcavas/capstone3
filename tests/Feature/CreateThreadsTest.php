<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Activity;


class CreateThreadsTest extends TestCase
{
    use DatabaseMigrations;

    /*@test*/
    function testGuestsMayNotCreateThreads()
    {
        $this->get('/threads/create')
            ->assertRedirect('/login');

        $this->post('/threads')
            ->assertRedirect('/login');
    }

    /*@test*/
    function testAuthenticatedUserCanCreateNewForumThreads()
    {
        //given we have a signed in user
        $this->signIn();

        //when we hit the endpoint to create a new thread
        //we use make instead of create because we don't need this object to persist
        $thread = make('App\Thread');

        $response = $this->post('/threads', $thread->toArray());

        //then, when we visit the thread page
        $this->get($response->headers->get('Location'))
        //we should see the new thread
            ->assertSee($thread->title)
            ->assertSee($thread->body);
    }

    /*@test*/
    function testThreadRequiresTitle()
    {
        $this->publishThread(['title' => null])
            ->assertSessionHasErrors('title');
    }

    /*@test*/
    function testThreadRequiresBody()
    {
        $this->publishThread(['body' => null])
            ->assertSessionHasErrors('body');
    }

    /*@test*/
    function testThreadRequiresValidChannel()
    {
        factory('App\Channel', 2)->create();

        $this->publishThread(['channel_id' => null])
            ->assertSessionHasErrors('channel_id');

        $this->publishThread(['channel_id' => 999])
            ->assertSessionHasErrors('channel_id');
    }

    /*@test*/
    function testUnauthorizedUsersMayNotDeleteThreads()
    {
        $thread = create('App\Thread');

        $this->delete($thread->path())->assertRedirect('/login');

        $this->signIn();
        $this->delete($thread->path())->assertStatus(403);
    }

    /*@test*/
    function testAuthorizedUsersMayDeleteThreads()
    {
        $this->signIn();

        //logged in user has to be the creator of the thread he wants to delete
        $thread = create('App\Thread', ['user_id' => auth()->id()]);
        $reply = create('App\Reply', ['thread_id' => $thread->id]);

        $response = $this->json('DELETE', $thread->path());

        $response->assertStatus(204);

        $this->withoutExceptionHandling()->assertDatabaseMissing('threads', ['id' => $thread->id]);
        $this->withoutExceptionHandling()->assertDatabaseMissing('replies', ['id' => $reply->id]);

        $this->assertEquals(0, Activity::count());
    }

    public function publishThread($overrides=[])
    {
        $this->signIn();

        $thread = make('App\Thread', $overrides);

        return $this->post('/threads', $thread->toArray());
    }
}
