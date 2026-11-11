@extends('layouts.app')

@section('title', 'Terms & Agreement')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<div class="ph-card p-4">
    @include('partials.terms-content')
</div>
@endsection