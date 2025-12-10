@extends('layouts.master')

@section('title', 'Create Booking')

@section('content')
    @livewire('meeting.booking-form', ['selectedRoomId' => $selectedRoomId ?? null])
@endsection
