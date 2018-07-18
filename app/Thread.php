<?php

namespace App;

use App\Filters\ThreadFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    //use the Trait RecordsActivity
    use RecordsActivity;

    //setting $guarded to an empty array fixes the MassAssignmentException on testing
    protected $guarded = [];

    //eager load the thread creator & channel info with the shown thread
    protected $with = ['creator', 'channel'];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('replyCount', function ($builder) {
            $builder->withCount('replies');
        });

        static::deleting(function ($thread){
            $thread->replies->each->delete();
            });
    }

    /**
     * get a string path for the thread
     * @return string
     */
    public function path()
    {
        /*return '/threads/' . $this->channel->slug . '/' . $this->id;*/
        return "/threads/{$this->channel->slug}/{$this->id}";
    }

    /**
     * a thread belongs to a creator
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * a thread belongs to a channel
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * a thread may have many replies
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    /**
     * add a reply to the thread
     * @param $reply
     */
    public function addReply($reply)
    {
        //this threw a MassAssignmentException during testing due to $reply being an array that's being passed to a create method
        $this->replies()->create($reply);
    }

    /**
     * @param Builder $query
     * @param ThreadFilters $filters
     * @return Builder
     */
    public function scopeFilter($query, ThreadFilters $filters)
    {
        return $filters->apply($query);
    }
}
