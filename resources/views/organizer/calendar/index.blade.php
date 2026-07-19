@extends('layouts.app')

@section('sidebar')
    @include('organizer.partials.sidebar')
@endsection

@section('content')

<div class="container py-4">

    <h2 class="mb-4">
        Calendar
    </h2>

    <div class="card">

        <div class="card-header">

            Google Calendar

        </div>

        <div class="card-body">

            @if($profile->google_calendar_connected)

                <div class="alert alert-success">

                    Google Calendar Connected

                </div>

                <form method="POST"
                      action="{{ route('organizer.calendar.sync') }}">

                    @csrf

                    <button class="btn btn-primary">

                        Sync Calendar

                    </button>

                </form>

                <form method="POST"
                      action="{{ route('organizer.calendar.disconnect') }}"
                      class="mt-3">

                    @csrf
                    @method('DELETE')

                    <button class="btn btn-danger">

                        Disconnect

                    </button>

                </form>

            @else

                <div class="alert alert-warning">

                    Google Calendar is not connected.

                </div>

                <a href="{{ route('organizer.calendar.connect') }}"
                   class="btn btn-primary">

                    Connect Google Calendar

                </a>

            @endif

        </div>

    </div>

</div>

@endsection