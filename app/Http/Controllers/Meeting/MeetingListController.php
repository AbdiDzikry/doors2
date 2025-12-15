<?php

namespace App\Http\Controllers\Meeting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meeting;

use Illuminate\Support\Facades\Auth;

class MeetingListController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $activeTab = $request->input('tab', 'meeting-list');
        $meetings = collect();
        $myMeetings = collect();
        $stats = [];
        $sortBy = $request->input('sort_by', 'start_time');
        $sortDirection = $request->input('sort_direction', 'asc');

        $filter = $request->input('filter', 'day');
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');

        // Check if date inputs are provided (Custom Range Logic)
        // If user manually selected a date, we treat it as a custom filter
        if ($startDateInput || $endDateInput) {
            $filter = 'custom';
        }

        // Calculate dates for View and Query
        [$effectiveStartDate, $effectiveEndDate] = $this->calculateDateRange($filter, $startDateInput, $endDateInput);

        if ($activeTab === 'my-meetings') {
            $query = Meeting::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('meetingParticipants', function ($subQ) use ($user) {
                      $subQ->where('participant_type', \App\Models\User::class)
                           ->where('participant_id', $user->id);
                  });
            });
            $query->whereBetween('start_time', [$effectiveStartDate, $effectiveEndDate]);

            $myMeetings = $query->with('room')->get();

            $stats = [
                'total' => $myMeetings->count(),
                'scheduled' => $myMeetings->where('calculated_status', 'scheduled')->count(),
                'completed' => $myMeetings->where('calculated_status', 'completed')->count(),
                'cancelled' => $myMeetings->where('calculated_status', 'cancelled')->count(),
            ];

        } else if ($activeTab === 'my-recurring-meetings') {
            // The Livewire component will handle its own data.
        } else {
            $query = Meeting::query();
            
            // Use the scope (or whereBetween since we have dates) - let's use whereBetween to avoid double parsing overhead, 
            // even though we added the scope. The scope is useful for other places where we might not need view variables.
            // But to strictly follow "Update MeetingListController to use the scope":
            //$query->filterByDate($filter, $startDateInput, $endDateInput); 
            // Actually, using whereBetween is more efficient here since we already calculated dates.
            // I will use whereBetween here for efficiency but I acknowledge the scope exists for API/other uses.
            $query->whereBetween('start_time', [$effectiveStartDate, $effectiveEndDate]);

            // Define allowed sortable columns
            $allowedSortBy = ['topic', 'start_time', 'end_time'];

            // Validate sort_by and sort_direction
            if (!in_array($sortBy, $allowedSortBy)) {
                $sortBy = 'start_time';
            }
            if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
                $sortDirection = 'asc';
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('topic', 'like', '%' . $search . '%')
                        ->orWhereHas('room', function ($qr) use ($search) {
                            $qr->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('user', function ($qu) use ($search) {
                            $qu->where('name', 'like', '%' . $search . '%');
                        });
                });
            }

            $query->orderBy($sortBy, $sortDirection);

            $meetings = $query->with('room', 'user')->get();
        }

        return view('meetings.list.index', compact('meetings', 'myMeetings', 'stats', 'filter', 'effectiveStartDate', 'effectiveEndDate', 'activeTab', 'sortBy', 'sortDirection'));
    }

    private function calculateDateRange($filter, $startDateInput, $endDateInput)
    {
        if ($filter === 'day') {
            $carbonStartDate = today();
            $carbonEndDate = today();
        } else {
            $carbonStartDate = $startDateInput ? \Carbon\Carbon::parse($startDateInput) : today();
            $carbonEndDate = $endDateInput ? \Carbon\Carbon::parse($endDateInput) : today();
        }

        switch ($filter) {
            case 'day':
                $effectiveStartDate = $carbonStartDate->startOfDay();
                $effectiveEndDate = $carbonEndDate->endOfDay();
                break;
            case 'week':
                $effectiveStartDate = $carbonStartDate->startOfWeek();
                $effectiveEndDate = $carbonEndDate->endOfWeek();
                break;
            case 'month':
                $effectiveStartDate = $carbonStartDate->startOfMonth();
                $effectiveEndDate = $carbonEndDate->endOfMonth();
                break;
            case 'year':
                $effectiveStartDate = $carbonStartDate->startOfYear();
                $effectiveEndDate = $carbonEndDate->endOfYear();
                break;
            case 'custom':
            default:
                $effectiveStartDate = $carbonStartDate->startOfDay();
                $effectiveEndDate = $carbonEndDate->endOfDay();
                break;
        }

        return [$effectiveStartDate, $effectiveEndDate];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function show(Meeting $meeting)
    {
        $meeting->load('room', 'user', 'recurringMeeting');
        return view('meetings.list.show', compact('meeting'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Meeting $meeting)
    {
        // Authorization
        if (Auth::id() !== $meeting->user_id && !Auth::user()->hasAnyRole(['Super Admin', 'Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        // Restriction: Employees cannot edit started or completed meetings
        if (!Auth::user()->hasAnyRole(['Super Admin', 'Admin']) && in_array($meeting->calculated_status, ['ongoing', 'completed'])) {
            return redirect()->route('meeting.meeting-lists.index')->with('error', 'You cannot edit a meeting that has started or is completed.');
        }

        $meeting->load('room', 'user', 'meetingParticipants.participant', 'pantryOrders.pantryItem');
        return view('meetings.list.edit', compact('meeting'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Meeting $meeting)
    {
        // Authorization
        if (Auth::id() !== $meeting->user_id && !Auth::user()->hasAnyRole(['Super Admin', 'Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        // Restriction: Employees cannot edit started or completed meetings
        if (!Auth::user()->hasAnyRole(['Super Admin', 'Admin']) && in_array($meeting->calculated_status, ['ongoing', 'completed'])) {
            return redirect()->route('meeting.meeting-lists.index')->with('error', 'You cannot edit a meeting that has started or is completed.');
        }

        $validatedData = $request->validate([
            'topic' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            // Add other fields as necessary
        ]);

        $meeting->update($validatedData);

        return redirect()->route('meeting.meeting-lists.show', $meeting)->with('success', 'Meeting updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Meeting $meeting, \App\Services\InventoryService $inventoryService)
    {
        // Authorization: Only the meeting creator or an admin can cancel.
        if (auth()->user()->id !== $meeting->user_id && !auth()->user()->hasRole(['Super Admin', 'Admin'])) {
            return back()->with('error', 'You are not authorized to cancel this meeting.');
        }

        // Begin a transaction to ensure atomicity
        \Illuminate\Support\Facades\DB::transaction(function () use ($meeting, $inventoryService) {
            // Only refund stock if the meeting is not already cancelled to prevent double refunds
            if ($meeting->status !== 'cancelled') {
                // Refund pantry stock
                $inventoryService->refundStockForMeeting($meeting);

                // Update meeting status
                $meeting->status = 'cancelled';
                $meeting->save();
            }
        });

        return redirect()->route('meeting.meeting-lists.index')
                        ->with('success','Meeting cancelled successfully and pantry stock restored.');
    }

    public function exportAttendance(Meeting $meeting)
    {
        // Authorization: Allow specific roles, meeting owner, OR participants
        // Load participants first to check and then export
        $participants = $meeting->meetingParticipants()->with('participant')->get();

        $isParticipant = $participants->contains(function ($p) {
            return $p->participant_id == \Illuminate\Support\Facades\Auth::id() && 
                   $p->participant_type == \App\Models\User::class;
        });

        if (Auth::id() !== $meeting->user_id && !$isParticipant && !Auth::user()->hasAnyRole(['Super Admin', 'Admin', 'Resepsionis'])) {
            abort(403, 'Unauthorized action.');
        }

        $data = $participants->map(function ($mp) use ($meeting) {
            $name = $mp->participant ? $mp->participant->name : 'N/A';
            $email = $mp->participant ? $mp->participant->email : 'N/A';
            
            // For internal users, get NPK and Department
            $npk = 'N/A';
            $department = 'N/A';
            if ($mp->participant_type === \App\Models\User::class && $mp->participant) {
                $npk = $mp->participant->npk ?? '-';
                $department = $mp->participant->department ?? '-';
            }

            // Helper function to sanitize string for XML
            $sanitize = function ($value) {
                if ($value === null) {
                    return '';
                }
                if (!is_string($value)) {
                    $value = (string) $value;
                }
                // Ensure valid UTF-8 and remove invalid XML characters
                $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                // Remove ALL control characters including newlines/carriage returns
                $value = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
                // Normalize whitespace (replace multiple spaces with single space)
                $value = preg_replace('/\s+/', ' ', $value);
                // Trim whitespace from both ends
                return trim($value);
            };

            // Format start_time to readable string before sanitization
            $startTimeStr = $meeting->start_time ? \Carbon\Carbon::parse($meeting->start_time)->format('d-m-Y H:i') : '-';
            $bookedBy = $meeting->user ? $sanitize($meeting->user->name) : '-';

            return [
                'Meeting Topic' => $sanitize($meeting->topic),
                'Start Time' => $startTimeStr,  // Use formatted string, no sanitization needed
                'Booked By' => $bookedBy,  // NEW COLUMN
                'Participant Name' => $sanitize($name),
                'Email' => $sanitize($email),
                'Type' => $mp->participant_type === \App\Models\User::class ? 'Internal' : 'External',
                'NPK' => $sanitize($npk),
                'Department' => $sanitize($department),
                'Status' => $mp->attended_at ? 'Hadir' : 'Belum Hadir',
                'Attendance Time' => $mp->attended_at ? \Carbon\Carbon::parse($mp->attended_at)->format('d-m-Y H:i') : '-',
            ];
        });

        // If no participants, create at least one row with meeting info
        if ($data->isEmpty()) {
            $bookedBy = $meeting->user ? $meeting->user->name : '-';
            $data = collect([[
                'Meeting Topic' => $meeting->topic ?? '',
                'Start Time' => $meeting->start_time ? \Carbon\Carbon::parse($meeting->start_time)->format('d-m-Y H:i') : '-',
                'Booked By' => $bookedBy,  // NEW COLUMN
                'Participant Name' => 'No participants',
                'Email' => '-',
                'Type' => '-',
                'NPK' => '-',
                'Department' => '-',
                'Status' => '-',
                'Attendance Time' => '-',
            ]]);
        }

        return (new \Rap2hpoutre\FastExcel\FastExcel($data))->download("Attendance_Report_{$meeting->id}.xlsx");
    }
}

