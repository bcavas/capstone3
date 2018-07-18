<?php

namespace App\Filters;

use App\User;
use Illuminate\Http\Request;

class ThreadFilters extends Filters
{
    protected $filters = ['by', 'popular'];

    /**
     * filter the query by a given username
     * @param $builder
     * @param $username
     * @return mixed
     */
    protected function by($username)
    {
        $user = User::where('name', $username)->firstOrFail();

        return $this->builder->where('user_id', $user->id);
    }

    /**
     * Filter the query according to most popular threads as measured by number of replies
     * @return mixed
     */
    protected function popular()
    {
        //clear out the default ordering being used
        $this->builder->getQuery()->orders = [];
        //so that we can let the new ordering criteria we set below take effect
        return $this->builder->orderBy('replies_count', 'desc');
    }
}