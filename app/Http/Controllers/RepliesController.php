<?php

namespace App\Http\Controllers;

use App\Reply;
use App\Thread;
use Illuminate\Http\Request;

class RepliesController extends Controller
{
    //limit access to only allow authenticated users
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store($channelId, Thread $thread)
    {
        $this->validate(request(), ['body' => 'required']);

        $thread->addReply([
            'body' => request('body'),
            'user_id' => auth()->id()
        ]);

        return back()
            ->with('flash', ' Your reply has been recorded.');
    }

    public function update(Reply $reply)
    {
        //references ReplyPolicy update method
        $this->authorize('update', $reply);

        $reply->update(request(['body']));
    }

    public function destroy(Reply $reply)
    {
        //references ReplyPolicy update method
        $this->authorize('update', $reply);

        $reply->delete();

        //if request is a result of AJAX request, do not redirect
        if (request()->expectsJson()) {
            return response(['status' => 'Reply deleted']);
        }

        return back()
            ->with('flash', ' Your reply has been deleted!');
    }
}
