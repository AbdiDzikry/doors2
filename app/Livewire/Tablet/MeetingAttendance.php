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
        $this->resetValidation(); // Clear stuck errors
        $this->errorMsg = '';
        
        $status = $this->attendanceStatus;
        if ($status === 'waiting') {
            $this->addError('inputNpk', 'Absensi belum dibuka.');
            return;
        }
        if ($status === 'closed') {
            $this->addError('inputNpk', 'Waktu absensi habis.');
            return;
        }

        // Find participant by NPK
        // We use the relation to get the User model with Pivot
        $participant = $this->meeting->participants()
                    ->where('npk', $this->inputNpk)
                    ->first();

        // Also check if Organizer is trying to check in (and if they are in the participant list)
        // If the organizer is not in the participant list, they technically can't "attend" via the pivot table.
        // Assuming Organizer IS a participant usually.
        
        if ($participant) {
            // Check-in logic
            if (is_null($participant->pivot->attended_at)) {
                $this->meeting->participants()->updateExistingPivot($participant->id, [
                    'attended_at' => Carbon::now(),
                    'status' => 'present'
                ]);
                $message = "Berhasil Check-in: " . $participant->name;
            } else {
                $message = "Anda sudah Check-in sebelumnya: " . $participant->name;
            }

            // Reset Input
            $this->inputNpk = '';
            
            // Dispatch success event for SweetAlert or Toast
            $this->dispatch('attendance-saved', $message);

        } else {
            // Check if it is Organizer but not in participant list
            if ($this->meeting->organizer && $this->meeting->organizer->npk === $this->inputNpk) {
                 // Organizer detected but not in list.
                 $this->dispatch('attendance-saved', "Selamat Datang Organizer: " . $this->meeting->organizer->name);
                 $this->inputNpk = '';
            } else {
                $this->addError('inputNpk', 'NPK tidak terdaftar di meeting ini.');
                // $this->errorMsg = 'NPK tidak terdaftar.'; // Removed to prevent duplicate alerts
            }
        }
    }

    public function getParticipantsListProperty()
    {
        // Return collection with pivot data
        return $this->meeting->meetingParticipants()
            ->with('participant')
            ->get()
            ->map(function ($mp) {
                // Determine Name and Dept
                if ($mp->participant_type === 'App\Models\User') {
                    $mp->name = $mp->participant->name ?? 'Unknown';
                    $mp->dept = 'Internal';
                } else {
                    $mp->name = $mp->participant->name ?? $mp->participant->email ?? 'Guest';
                    $mp->dept = 'External';
                }
                return $mp;
            });
    }

    public function render()
    {
        return view('livewire.tablet.meeting-attendance', [
            'participantsList' => $this->participantsList,
            'status' => $this->attendanceStatus
        ]);
    }
}
