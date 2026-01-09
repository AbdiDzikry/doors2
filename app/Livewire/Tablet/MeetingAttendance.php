<?php

namespace App\Livewire\Tablet;

use Livewire\Component;
use App\Models\Meeting;
use App\Models\MeetingParticipant;
use Carbon\Carbon;

class MeetingAttendance extends Component
{
    public Meeting $meeting;
    public $inputNpk = '';
    public $attendanceData = []; // [participant_id => bool]
    public $isVerified = false;
    public $showModal = false;
    public $errorMsg = '';

    public function mount(Meeting $meeting)
    {
        $this->meeting = $meeting;
    }

    public function getAttendanceStatusProperty()
    {
        $now = Carbon::now();
        $startTime = Carbon::parse($this->meeting->start_time);
        $endTimePlus30 = Carbon::parse($this->meeting->end_time)->addMinutes(30);

        if ($now->lt($startTime)) {
            return 'waiting';
        }

        if ($now->gt($endTimePlus30)) {
            return 'closed';
        }

        return 'open';
    }

    public function verifyNpk()
    {
        $this->errorMsg = ''; // Reset error

        // Double check time window on server side action
        $status = $this->attendanceStatus;
        if ($status === 'waiting') {
            $this->addError('inputNpk', 'Absensi belum dibuka. Tunggu hingga meeting dimulai.');
            return;
        }
        if ($status === 'closed') {
            $this->addError('inputNpk', 'Batas waktu absensi telah berakhir (30 menit setelah selesai).');
            return;
        }

        // 1. Check if Organizer
        $isOrganizer = $this->meeting->organizer && $this->meeting->organizer->npk === $this->inputNpk;

        // 2. Check if PIC (Participant with is_pic = true)
        $isPic = $this->meeting->participants()
                    ->where('npk', $this->inputNpk)
                    ->where('is_pic', true)
                    ->exists();

        if ($isOrganizer || $isPic) {
            $this->isVerified = true;
            $this->showModal = true;
            $this->loadParticipants();
        } else {
            $this->addError('inputNpk', 'Akses Ditolak. NPK tidak terdaftar sebagai Organizer atau PIC.');
            $this->errorMsg = 'Akses Ditolak. NPK tidak terdaftar sebagai Organizer atau PIC.';
        }
    }

    public function loadParticipants()
    {
        // Get all participants
        $participants = $this->meeting->participants()->get();

        foreach ($participants as $p) {
            // Check if attended_at is filled
            $this->attendanceData[$p->id] = !is_null($p->pivot->attended_at);
        }
    }

    public function saveAttendance()
    {
        foreach ($this->attendanceData as $participantId => $isPresent) {
            $pivot = MeetingParticipant::where('meeting_id', $this->meeting->id)
                        ->where('user_id', $participantId) 
                        ->first();
            
            $participant = $this->meeting->participants()->find($participantId);
            
            if ($participant) {
                if ($isPresent) {
                    if (is_null($participant->pivot->attended_at)) {
                        $this->meeting->participants()->updateExistingPivot($participantId, [
                            'attended_at' => Carbon::now(),
                            'status' => 'present' 
                        ]);
                    }
                } else {
                    $this->meeting->participants()->updateExistingPivot($participantId, [
                        'attended_at' => null,
                        'status' => 'invited' 
                    ]);
                }
            }
        }

        $this->showModal = false;
        $this->inputNpk = ''; // Reset security
        $this->isVerified = false;

        // Dispatch alert
        $this->dispatch('attendance-saved', 'Data absensi berhasil disimpan!');
    }

    public function close()
    {
        $this->showModal = false;
        $this->inputNpk = '';
        $this->isVerified = false;
    }

    public function render()
    {
        return view('livewire.tablet.meeting-attendance', [
            'participantsList' => $this->isVerified ? $this->meeting->participants()->get() : [],
            'status' => $this->attendanceStatus // Pass computed property to view
        ]);
    }
}
