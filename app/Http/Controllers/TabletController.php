<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TabletController extends Controller
{
    public function index()
    {
        $rooms = Room::all();
        return view('tablet.index', compact('rooms'));
    }

    public function show($id)
    {
        $room = \App\Models\Room::findOrFail($id);
        
        // Fetch ALL meetings for today for the Schedule List Tab
        // Fetch ALL meetings for today for the Schedule List Tab
        // Sort Order: Ongoing (DESC check-in?) -> Scheduled (ASC time) -> Completed (DESC time)
        $todaysMeetings = $room->meetings()
            ->whereIn('status', ['scheduled', 'ongoing', 'completed'])
            ->whereDate('start_time', now())
            ->with(['user', 'meetingParticipants.participant'])
            ->get()
            ->sortBy(function ($meeting) {
                // Primary Sort: Status Priority
                // 1. Ongoing
                // 2. Scheduled
                // 3. Completed
                
                // Note: We use calculated_status logic if needed, but DB status is safer for sorting if reliable.
                // However, 'scheduled' in past might be visually 'completed'.
                // Let's use the same logic as the view: calculated_status.
                
                $status = $meeting->calculated_status; 
                // We need to access the attribute or calculate it here. 
                // Since calculated_status is an accessor, ensure it's available or replicate logic.
                
                switch ($status) {
                    case 'ongoing': return 1;
                    case 'scheduled': return 2;
                    case 'completed': return 3;
                    default: return 4;
                }
            })->values(); // Reset keys

        // Secondary Sort (Manual because sortBy is stable but we want mixed directions)
        // Actually, we can use a complex sort key or split and merge.
        
        $ongoing = $todaysMeetings->where('calculated_status', 'ongoing')->sortBy('start_time');
        $scheduled = $todaysMeetings->where('calculated_status', 'scheduled')->sortBy('start_time');
        $completed = $todaysMeetings->where('calculated_status', 'completed')->sortByDesc('end_time'); // Most recently finished first
        
        $todaysMeetings = $ongoing->merge($scheduled)->merge($completed);

        // Current meeting for status indicator logic in sidebar
        $currentMeeting = $room->meetings()
            ->whereIn('status', ['scheduled', 'ongoing'])
            ->where('start_time', '<=', now())
            ->where('end_time', '>', now())
            ->first();

        // Calculate Smart Default Start Time
        $defaultStartTime = now();
        if ($currentMeeting) {
            $defaultStartTime = \Carbon\Carbon::parse($currentMeeting->end_time);
        }

        // Round up to nearest 15 minutes
        $minute = $defaultStartTime->minute;
        $remainder = $minute % 15;
        if ($remainder > 0) {
            $defaultStartTime->addMinutes(15 - $remainder);
        }
        $defaultStartTime->second(0);

        // If rounded time is in past (e.g. now=10:02 -> 10:15, but if we used end_time it might be exact), ensure it's future
        if ($defaultStartTime->isPast()) {
             $defaultStartTime = now()->addMinutes(15 - (now()->minute % 15))->second(0);
        }

        $defaultHour = $defaultStartTime->format('H');
        $defaultMinute = $defaultStartTime->format('i');

        // Prepare occupied slots for frontend (AlpineJS)
        $occupiedSlots = $todaysMeetings->map(function ($meeting) {
            $start = \Carbon\Carbon::parse($meeting->start_time);
            $end = \Carbon\Carbon::parse($meeting->end_time);
            return [
                'start_minutes' => $start->hour * 60 + $start->minute,
                'end_minutes' => $end->hour * 60 + $end->minute,
            ];
        })->values();

        return view('tablet.show', compact('room', 'todaysMeetings', 'currentMeeting', 'defaultHour', 'defaultMinute', 'occupiedSlots'));
    }

    public function store(Request $request, $id)
    {
        // Force cast to integer to prevent "must be an integer" validation error on strings like "09"
        $request->merge([
            'start_hour' => (int) $request->start_hour,
            'start_minute' => (int) $request->start_minute,
            'duration' => (int) $request->duration,
        ]);

        $request->validate([
            'npk' => 'required|exists:users,npk',
            'topic' => 'required|string|max:255',
            'start_date' => 'required|date',
            'start_hour' => 'required|integer|min:0|max:23',
            'start_minute' => 'required|integer|in:0,15,30,45',
            'duration' => 'required|integer|min:15',
            'internal_participants' => 'nullable', // Now accepts JSON string or array
            'external_participants' => 'nullable',
        ]);

        $room = \App\Models\Room::findOrFail($id);
        $user = \App\Models\User::where('npk', $request->npk)->first();
        
        \Illuminate\Support\Facades\Log::info('--- TABLET BOOKING DEBUG ---');
        \Illuminate\Support\Facades\Log::info('Raw Internal Payload:', ['data' => $request->input('internal_participants')]);
        \Illuminate\Support\Facades\Log::info('Raw External Payload:', ['data' => $request->input('external_participants')]);
        
        // Construct Start Time
        $startTimeString = $request->start_date . ' ' . str_pad($request->start_hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($request->start_minute, 2, '0', STR_PAD_LEFT) . ':00';
        $startTime = \Carbon\Carbon::parse($startTimeString);
        $endTime = (clone $startTime)->addMinutes((int) $request->duration);

        if ($startTime->isPast()) {
             // Allow 5 min tolerance
             if ($startTime->diffInMinutes(now()) > 5) {
                 return back()->with('error', 'Waktu mulai tidak boleh di masa lalu.');
             }
        }

        // Check conflicts
        $conflict = $room->meetings()
            ->whereIn('status', ['scheduled', 'ongoing'])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
            })
            ->exists();

        if ($conflict) {
            return back()->with('error', 'Ruangan tidak tersedia untuk waktu yang dipilih.');
        }

        try {
            DB::beginTransaction();

            // Create Meeting
            $meeting = \App\Models\Meeting::create([
                'topic' => $request->topic,
                'description' => 'Booked via Room Display (Kiosk)',
                'room_id' => $room->id,
                'user_id' => $user->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'status' => 'scheduled',
                'type' => 'offline',
                'confirmation_status' => 'confirmed', // Auto-confirm walk-in/tablet bookings
            ]);
            
            // Add organizer as participant
            \App\Models\MeetingParticipant::create([
                'meeting_id' => $meeting->id,
                'participant_id' => $user->id,
                'participant_type' => \App\Models\User::class,
                'status' => 'pending'
            ]);

            // Process Internal Participants (IDs)
            if ($request->filled('internal_participants')) {
                $input = $request->internal_participants;
                \Illuminate\Support\Facades\Log::info('Processing Internal Participants: ', ['type' => gettype($input), 'value' => $input]);

                if (is_string($input)) {
                    $ids = json_decode($input, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                         // Try decoding assuming it's a list from Livewire like "[1,2]" string
                         $ids = explode(',', str_replace(['[', ']', '"'], '', $input));
                    }
                } else {
                    $ids = $input;
                }

                if (is_array($ids)) {
                    foreach ($ids as $userId) {
                        // Flatten if nested array (e.g. [[1,2]])
                        if (is_array($userId)) {
                            foreach ($userId as $nestedId) {
                                if (!$nestedId || $nestedId == $user->id) continue;
                                 \App\Models\MeetingParticipant::firstOrCreate([
                                    'meeting_id' => $meeting->id,
                                    'participant_id' => trim((string)$nestedId),
                                    'participant_type' => \App\Models\User::class
                                ], ['status' => 'pending']);
                            }
                            continue;
                        }

                        if (!$userId || $userId == $user->id) continue;
                        \App\Models\MeetingParticipant::firstOrCreate([
                            'meeting_id' => $meeting->id,
                            'participant_id' => trim((string)$userId),
                            'participant_type' => \App\Models\User::class
                        ], ['status' => 'pending']);
                    }
                }
            }

            // Process External Participants (IDs)
            if ($request->filled('external_participants')) {
                $input = $request->external_participants;
                if (is_string($input)) {
                    $ids = json_decode($input, true);
                } else {
                    $ids = $input;
                }

                if (is_array($ids)) {
                    foreach ($ids as $extId) {
                        if (!$extId) continue;
                        \App\Models\MeetingParticipant::firstOrCreate([
                            'meeting_id' => $meeting->id,
                            'participant_id' => trim($extId),
                            'participant_type' => \App\Models\ExternalParticipant::class
                        ], ['status' => 'pending']);
                    }
                }
            }
            
            DB::commit();
            return back()->with('success', 'Booking Berhasil!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Tablet Booking Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses booking: ' . $e->getMessage());
        }
    }

    public function checkIn(Request $request, $id)
    {
        $request->validate(['npk' => 'required|exists:users,npk']);
        $meeting = \App\Models\Meeting::findOrFail($id);
        $user = \App\Models\User::where('npk', $request->npk)->first();

        // Check if user is participant
        $participant = \App\Models\MeetingParticipant::where('meeting_id', $meeting->id)
            ->where('participant_id', $user->id)
            ->where('participant_type', \App\Models\User::class)
            ->first();

        if (!$participant) {
            // Optional: Allow walk-in attendance? For now strictly enforce participant list.
            // Or just check if they are the organizer?
            if ($meeting->user_id === $user->id) {
                 // Create participant entry if missing for organizer (shouldn't happen but safe to handle)
                 $participant = \App\Models\MeetingParticipant::create([
                    'meeting_id' => $meeting->id,
                    'participant_id' => $user->id,
                    'participant_type' => \App\Models\User::class,
                    'status' => 'pending'
                ]);
            } else {
                return back()->with('error', 'Anda tidak terdaftar dalam meeting ini.');
            }
        }

        if ($participant->attended_at) {
            return back()->with('info', 'Anda sudah melakukan absensi.');
        }

        $participant->update([
            'attended_at' => now(),
            'status' => 'attended'
        ]);

        // If Organizer checks in, Start the Meeting
        if ($meeting->user_id === $user->id && $meeting->status === 'scheduled') {
            $meeting->update(['status' => 'ongoing']);
        }

        return back()->with('success', 'Absensi Berhasil! Selamat datang ' . $user->name);
    }

    public function cancel(Request $request, $id)
    {
        $request->validate(['npk' => 'required|exists:users,npk']);
        $meeting = \App\Models\Meeting::findOrFail($id);
        $user = \App\Models\User::where('npk', $request->npk)->first();

        $isOrganizer = $meeting->user_id === $user->id;
        $isPic = $meeting->meetingParticipants()
            ->where('participant_id', $user->id)
            ->where('participant_type', \App\Models\User::class)
            ->where('is_pic', true)
            ->exists();

        if (!$isOrganizer && !$isPic) {
            return back()->with('error', 'Hanya Organizer atau PIC yang dapat membatalkan.');
        }

        $meeting->update(['status' => 'cancelled']);

        return back()->with('success', 'Meeting berhasil dibatalkan.');
    }

    public function batteryAlert(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'level' => 'required|integer'
        ]);

        $room = Room::find($request->room_id);
        
        // Log locally
        \Illuminate\Support\Facades\Log::warning("TABLET BATTERY LOW: Room {$room->name} is at {$request->level}%");

        // Send to Receptionists & Super Admins
        $recipients = User::role(['Resepsionis', 'Super Admin'])->get();
        if ($recipients->isNotEmpty()) {
            \Illuminate\Support\Facades\Notification::send($recipients, new \App\Notifications\TabletBatteryLow($room, $request->level));
        }

        return response()->json(['status' => 'ok']);
    }

    public function checkUpdates(Request $request, $id)
    {
        $room = Room::findOrFail($id);
        
        // 1. Data Changes: Check ANY meeting from today onwards
        $lastMeetingUpdate = $room->meetings()
            ->where('start_time', '>=', now()->startOfDay())
            ->latest('updated_at')
            ->value('updated_at');

        // 2. Room Data Changes
        $roomUpdate = $room->updated_at;

        // 3. Time-Based State Changes: Identify who is "Current" right now
        // This ensures that when time crosses a meeting start/end, the hash changes.
        $currentMeetingId = $room->meetings()
            ->whereIn('status', ['scheduled', 'ongoing'])
            ->where('start_time', '<=', now())
            ->where('end_time', '>', now())
            ->value('id');

        // 4. Participant Attendance Changes
        // Check for latest update timestamp in meeting_participants table for relevant meetings
        $lastParticipantUpdate = \Illuminate\Support\Facades\DB::table('meeting_participants')
            ->join('meetings', 'meetings.id', '=', 'meeting_participants.meeting_id')
            ->where('meetings.room_id', $id)
            ->where('meetings.start_time', '>=', now()->startOfDay())
            ->max('meeting_participants.updated_at');

        // Generate Hash (Data Timestamp + Room Timestamp + Current Active Meeting ID + Participant Timestamp)
        $hashString = ($lastMeetingUpdate ? $lastMeetingUpdate->timestamp : '0') 
            . '|' . $roomUpdate->timestamp 
            . '|' . ($currentMeetingId ?? 'none')
            . '|' . ($lastParticipantUpdate ? strtotime($lastParticipantUpdate) : '0');
            
        $hash = md5($hashString);

        return response()->json([
            'hash' => $hash,
            'timestamp' => now()->timestamp
        ]);
    }
}
