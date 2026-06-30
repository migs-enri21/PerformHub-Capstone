@if(auth()->user()->isPerformer())
    @include('performer.partials.sidebar')
@elseif(auth()->user()->isOrganizer())
    @include('organizer.partials.sidebar')
@elseif(auth()->user()->isAdmin())
    @include('admin.partials.sidebar')
@endif
