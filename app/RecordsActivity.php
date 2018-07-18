<?php
/**
 * Created by PhpStorm.
 * User: Benjamin Cavas III
 * Date: 30/06/2018
 * Time: 8:33 AM
 */

namespace App;


trait RecordsActivity
{
    protected static function bootRecordsActivity()
    {
        //if guest, don't do anything
        if (auth()->guest()) return;

        foreach(static::getActivitiesToRecord() as $event){
            static::$event(function ($model) use ($event){
                $model->recordActivity($event);
            });
        }

        static::deleting(function($model) {
            $model->activity()->delete();
        });
    }

    //listen for thread creation and deletion using firemodel events on model.php
    protected static function getActivitiesToRecord()
    {
        return ['created'];
    }

    protected function recordActivity($event)
    {
        $this->activity()->create([
            'user_id' => auth()->id(),
            'type' => $this->getActivityType($event)
        ]);
    }

    public function activity()
    {
        //morphMany functions like hasMany except it doesn't hard-code the model relationship
        return $this->morphMany('App\Activity', 'subject');
    }

    protected function getActivityType($event)
    {
        $type = strtolower((new \ReflectionClass($this))->getShortName());

        return "{$event}_{$type}";
    }
}