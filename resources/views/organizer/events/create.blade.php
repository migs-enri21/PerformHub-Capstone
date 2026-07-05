@extends('layouts.app')

@section('title', 'Create Event')

@section('sidebar')
@include('organizer.partials.sidebar')
@endsection

@section('content')

<div class="container">

    <h2 class="fw-bold mb-4">Event Information</h2>

    <div class="ph-card p-4">
        

            <form>

                <div class="mb-3"><label class="form-label">Event Name</label><input type="text" class="form-control" name="event_name"></div>

                <div class="row">

                    <div class="col-md-6 mb-3"><label class="form-label">Event Type</label><select class="form-select" name="event_type_id">
                        <option>Select Event Type</option></select>
                    </div>

                    <div class="col-md-6 mb-3"><label class="form-label">Preferred Performer Category</label><select class="form-select" name="category_id">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3"><label class="form-label">Event Date</label><input type="date" class="form-control" name="event_date"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Event Time</label><input type="time" class="form-control" name="event_time"></div>

                </div>

                <div class="mb-3"><label class="form-label">Venue / Location</label><input type="text" class="form-control" name="venue"></div>

                <div class="row">

                    <div class="col-md-6 mb-3"><label class="form-label">Budget (₱)</label><input type="number" class="form-control" name="budget"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Performance Duration</label><input type="text" class="form-control" name="duration"></div>

                </div>

                <div class="mb-4"><label class="form-label">Special Requirements</label><textarea class="form-control" rows="4" name="requirements"></textarea></div>

                <div class="d-flex justify-content-end">

                    <a href="{{ route('organizer.dashboard') }}" class="btn ph-btn-secondary me-2">Cancel</a>
                    <button class="btn ph-btn-primary">Create Event & Find Performers</button>

                </div>

            </form>

        
    </div>

</div>

@endsection