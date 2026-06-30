<link href="{{ asset('css/performhub-base.css') }}" rel="stylesheet">
@auth
    @if(auth()->user()->isPerformer())
        <link href="{{ asset('css/performer.css') }}" rel="stylesheet">
    @elseif(auth()->user()->isOrganizer())
        <link href="{{ asset('css/organizer.css') }}" rel="stylesheet">
        <link href="{{ asset('css/performer.css') }}" rel="stylesheet">
    @elseif(auth()->user()->isAdmin())
        <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    @endif
@else
    <link href="{{ asset('css/organizer.css') }}" rel="stylesheet">
@endauth
