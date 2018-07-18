<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;


class ReadThreadsTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();

        $this->thread = factory('App\Thread')->create();
    }

    /*@test*/
    public function testThreadsIndex()
    {
        //when we view all threads
        $this->withoutExceptionHandling()->get('/threads')
            //we should see all the threads' titles
            ->assertSee($this->thread->title);
    }
    /*@test*/
    public function testThreadsShow()
    {
        //when we view an individual thread
        $this->withoutExceptionHandling()->get($this->thread->path())
            //we should see its title
            ->assertSee($this->thread->title);
    }
    /*@test*/
    public function testThreadReplies()
    {
        $reply = create('App\Reply', ['thread_id' => $this->thread->id]);

        //when we view an individual thread
        $this->withoutExceptionHandling()->get($this->thread->path())
            //we should see its associated replies
            ->assertSee($reply->body);
    }

    /*@test*/
    function testUserCanFilterThreadsByChannel()
    {
        $channel = create('App\Channel');

        //create a thread associated with current channel
        $threadInChannel = create('App\Thread', ['channel_id' => $channel->id]);

        //create another thread that belongs to another channel
        $threadNotInChannel = create('App\Thread');

        //when user visits the channel page
        $this->withoutExceptionHandling()->get('/threads/' . $channel->slug)
            //they should see the threads associated with the channel
            ->assertSee($threadInChannel->title)
            ->assertDontSee($threadNotInChannel->title);
    }

    /*@test*/
    function testUserCanFilterThreadsByUsername()
    {
        $this->signIn(create('App\User', ['name' => 'test']));

        $threadByTest = create('App\Thread', ['user_id' => auth()->id()]);
        $threadNotByTest = create('App\Thread');

        $this->withoutExceptionHandling()->get('threads?by=test')
            ->assertSee($threadByTest->title)
            ->assertDontSee($threadNotByTest->title);
    }

    /*@test*/
    function testUserCanFilterThreadsByPopularity()
    {
        //given we have three threads
        //with 2 replies, 3replies, and 0 replies, respectively
        $threadWithTwoReplies = create('App\Thread');
        create('App\Reply', ['thread_id' => $threadWithTwoReplies->id], 2);

        $threadWithThreeReplies = create('App\Thread');
        create('App\Reply', ['thread_id' => $threadWithThreeReplies->id], 3);

        $threadWithNoReplies = $this->thread;

        //when I filter all threads by popularity
        $response = $this->getJson('threads?popular=1')->json();

        //Then they should be returned from most replies to least
        $this->assertEquals([3, 2, 0], array_column($response, 'replies_count'));
    }
}
