@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
<div class="container mx-auto px-6 py-8">
    <div class="bg-white rounded-lg shadow-xl p-8 relative overflow-hidden">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Welcome to DOORS</h1>
            </div>
            <img src="{{ asset('logo_dharma.png') }}" alt="Dharma Polimetal" class="h-16 object-contain">
        </div>
        <p class="text-gray-600">
            Hello, {{ Auth::user()->name }}. Your role has not been assigned a specific dashboard.
        </p>
        <p class="mt-4">
            Please contact an administrator if you believe this is an error. You can manage your profile by clicking on your name in the top-right corner.
        </p>
    </div>
</div>
@endsection
