@extends('layouts.master')

@section('title', 'Edit Meeting')

@section('content')
    {{-- Refactored to use the shared Livewire BookingForm component --}}
    @livewire('meeting.booking-form', ['meeting' => $meeting])
@endsection
