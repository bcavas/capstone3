<?php

namespace Tests\Unit;

use Illuminate\Support\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Activity;

class ActivityTest extends TestCase
{
    use DatabaseMigrations;

    /*@test*/
    public function testCreateThreadIsRecordedActivity()
    {
        $this->signIn();

        $thread = create('App\Thread');

        $this->withoutExceptionHandling()->assertDatabaseHas('activities', [
            'type' => 'created_thread',
            'user_id' => auth()->id(),
            'subject_id' => $thread->id,
            'subject_type' => 'App\Thread'
        ]);

        //reference the activity model and get the first entry in the database
        $activity = Activity::first();

        //test relationship of activity and subject
        $this->assertEquals($activity->subject->id, $thread->id);
    }

    /*@test*/
    function testRepliesRecordedActivity()
    {
        $this->signIn();

        $reply = create('App\Reply');

        $this->assertEquals(2, Activity::count());
    }

    /*@test*/
    function testFetchFeedForAnyUser()
    {
        $this->signIn();

        //given we have 2 threads
        create('App\Thread', ['user_id' => auth()->id()], 2);

        //and one of them is from a week ago
        auth()->user()->activity()->first()->update(['created_at' => Carbon::now()->subWeek()]);

        //when we fetch their feed
        $feed = Activity::feed(auth()->user(), 50);

        //then it should be returned in the proper format
        $this->withoutExceptionHandling()->assertTrue($feed->keys()->contains(
            Carbon::now()->format('Y-m-d')
        ));

        $this->withoutExceptionHandling()->assertTrue($feed->keys()->contains(
            Carbon::now()->subWeek()->format('Y-m-d')
        ));
    }
}
