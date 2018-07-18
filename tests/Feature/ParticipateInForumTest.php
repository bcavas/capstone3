<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;


class ParticipateInForumTest extends TestCase
{
    use DatabaseMigrations;

    /*@test*/
    function testUnauthUserMayNotReply()
    {
        $this->post('/threads/some-channel/1/replies', [])
            ->assertRedirect('/login');
    }

    /*@test*/
    function testAuthUserPartInForumThreads()
    {
        //given we have an authenticated user
        $this->signIn();

        //and an existing thread
        $thread = create('App\Thread');

        //when the user adds a reply to the thread
        $reply = make('App\Reply');
        //simulate a post request to server
        $this->post($thread->path().'/replies', $reply->toArray());

        //then their reply should be visible on the page
        $this->get($thread->path())
            ->assertSee($reply->body);
    }

    /*@test*/
    function testReplyRequiresBody()
    {
        $this->signIn();

        $thread = create('App\Thread');
        $reply = make('App\Reply', ['body' => null]);

        $this->post($thread->path().'/replies', $reply->toArray())
            ->assertSessionHasErrors('body');
    }

    /*@test*/
    function testUnauthorizedUsersCannotDeleteReplies()
    {
        $reply = create('App\Reply');

        $this->delete("/replies/{$reply->id}")
            ->assertRedirect('login');

        $this->signIn()
            ->delete("/replies/{$reply->id}")
            ->assertStatus(403);
    }

    /*@test*/
    function testAuthorizedUsersCanDeleteReplies()
    {
        $this->signIn();
        $reply = create('App\Reply', ['user_id' => auth()->id()]);

        $this->delete("/replies/{$reply->id}");

        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);
    }

    /*@test*/
    function testUnauthorizedUsersCannotUpdateReplies()
    {
        $reply = create('App\Reply');

        $this->patch("/replies/{$reply->id}")
            ->assertRedirect('login');

        $this->signIn()
            ->patch("/replies/{$reply->id}")
            ->assertStatus(403);
    }

    /*@test*/
    function testAuthorizedUsersCanUpdateReplies()
    {
        $this->signIn();

        $reply = create('App\Reply', ['user_id' => auth()->id()]);

        $updatedReply = 'You been changed, fool!';
        $this->patch("/replies/{$reply->id}", ['body' => $updatedReply]);

        $this->assertDatabaseHas('replies', ['id' => $reply->id, 'body' => $updatedReply]);
    }
}
