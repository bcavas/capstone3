<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use Favoritable, RecordsActivity;

    //fix mass assignment exception during testing
    protected $guarded = [];

    //eager load owner and favorites with every reply queried
    protected $with = ['owner', 'favorites'];

    //appends specific custom attributes when you cast to json (both from the Favoritable trait)
    protected $appends = ['favoritesCount', 'isFavorited'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    public function path()
    {
        return $this->thread->path() . "#reply-{$this->id}";
    }
}
