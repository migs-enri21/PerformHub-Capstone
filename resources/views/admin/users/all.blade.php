@extends('layouts.app')

@section('title', 'All Users')

@section('sidebar')
@include('admin.partials.sidebar')
@endsection

@section('content')
<h2 class="fw-bold mb-4">All Users</h2>
<div class="ph-card p-4">
    <table class="table table-dark table-hover table-sm mb-0">
        <thead>
            <tr>
                <th>Name</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ Str::title($user->name) }}</td>
                    <td>{{ ucfirst($user->role) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
