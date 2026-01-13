@extends('layouts.master')

@section('title', 'Create Booking')

@section('content')
    @livewire('meeting.booking-form', [
        'selectedRoomId' => $selectedRoomId ?? null,
        'start_time' => $startTime ?? null,
        'end_time' => $endTime ?? null
    ])
@endsection
