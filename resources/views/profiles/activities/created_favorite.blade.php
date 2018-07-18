@component('profiles.activities.activity')
    @slot('heading')
        <a href="{{$activity->subject->favorited->path()}}">
            {{$profileUser->name}} tagged a reply as a favorite.
        </a>
        {{--<a href="{{$activity->subject->thread->path()}}">"{{$activity->subject->thread->title}}"</a>--}}
    @endslot

    @slot('body')
        {{ $activity->subject->favorited->body }}
    @endslot
@endcomponent
