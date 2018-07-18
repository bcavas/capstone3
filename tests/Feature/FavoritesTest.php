<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FavoritesTest extends TestCase
{
    use DatabaseMigrations;

    /*@test*/
    public function testGuestsCannotFavoriteAnything()
    {
        $this->post('replies/1/favorites')
            ->assertRedirect('/login');
    }

    /*@test*/
    public function testAuthenticatedUserCanFavoriteAnyReply()
    {
        //a user is logged in
        $this->signIn();

        //create a test reply
        $reply = create('App\Reply');

        //if user posts to a "favorite" endpoint
        $this->post('replies/' . $reply->id . '/favorites');

        //it shoudl be recorded in the database
        $this->assertCount(1, $reply->favorites);
    }

    /*@test*/
    public function testAuthenticatedUserCanUnfavoriteAnyReply()
    {
        //a user is logged in
        $this->signIn();

        //create a test reply
        $reply = create('App\Reply');

        //if user posts to a "favorite" endpoint
        $reply->favorite();

        //then toggles the favorite off
        $this->delete('replies/' . $reply->id . '/favorites');

        //it should be removed from the database
        $this->assertCount(0, $reply->favorites);
    }

    /*@test*/
    function testAuthenticatedUserMayOnlyFavoriteAReplyOnce()
    {
        $this->signIn();

        $reply = create('App\Reply');

        //if user posts multiple times to a "favorite" endpoint
        $this->post('replies/' . $reply->id . '/favorites');
        $this->post('replies/' . $reply->id . '/favorites');

        //it shoud be recorded only once in the database
        $this->assertCount(1, $reply->favorites);
    }
}
