<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ThreadTest extends TestCase
{
    use DatabaseMigrations;

    protected $thread;

    //generate thread for all tests here
    public function setUp()
    {
        parent::setUp();

        $this->thread = create('App\Thread');
    }

    /*@test*/
    function testThreadCanMakeStringPath()
    {
        $thread = create('App\Thread');
        $this->withExceptionHandling();
        $this->assertEquals("/threads/{$thread->channel->slug}/{$thread->id}", $thread->path());
    }

    /*@test*/
    function testThreadReplies()
    {
        //test that thread has replies
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $this->thread->replies);
    }

    /*@test*/
    function testThreadCreator()
    {
        $this->assertInstanceOf('App\User', $this->thread->creator);
    }

    /*@test*/
    public function testThreadCanAddReply()
    {
        $this->thread->addReply([
            'body' => 'Foobar',
            'user_id' => 1
        ]);

        $this->assertCount(1, $this->thread->replies);
    }

    /*@test*/
    function testThreadBelongsToChannel()
    {
        $thread = create('App\Thread');

        $this->withoutExceptionHandling();
        $this->assertInstanceOf('App\Channel', $thread->channel);
    }
}
