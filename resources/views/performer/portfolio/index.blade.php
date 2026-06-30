@extends('layouts.app')

@section('title', 'Portfolio')

@section('sidebar')
@include('performer.partials.sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">Portfolio</h2>

<div class="ph-card p-4 mb-4">
    <h5 class="fw-semibold mb-3">Upload Photo or Video</h5>
    <form method="POST" action="{{ route('performer.portfolio.store') }}" enctype="multipart/form-data" class="row g-3">
        @csrf
        <div class="col-md-3">
            <select name="type" class="form-select ph-input" required>
                <option value="photo">Photo</option>
                <option value="video">Video</option>
            </select>
        </div>
        <div class="col-md-4">
            <input type="file" name="file" class="form-control ph-input" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="title" class="form-control ph-input" placeholder="Title">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn ph-btn-primary w-100">Upload</button>
        </div>
    </form>
</div>

<div class="row g-4">
    @forelse($portfolios as $item)
        <div class="col-md-4">
            <div class="ph-card overflow-hidden">
                @if($item->type === 'photo')
                    <img src="{{ asset('storage/'.$item->file_path) }}" class="w-100" style="height:200px;object-fit:cover;" alt="">
                @else
                    <video src="{{ asset('storage/'.$item->file_path) }}" class="w-100" style="height:200px;object-fit:cover;" controls></video>
                @endif
                <div class="p-3">
                    <h6 class="mb-1">{{ $item->title ?? ucfirst($item->type) }}</h6>
                    <form action="{{ route('performer.portfolio.destroy', $item) }}" method="POST">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Remove</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-muted">No portfolio items yet.</div>
    @endforelse
</div>
@endsection
