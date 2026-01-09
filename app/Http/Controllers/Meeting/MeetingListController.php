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

        $filter = $request->input('filter', 'day'); // Default to Today
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
            
            // Date Filter
            $query->whereBetween('start_time', [$effectiveStartDate, $effectiveEndDate]);

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

            // Get Collection
            $meetings = $query->with('room', 'user')->get();

            // 1. Filter by Status (calculated_status)
            if ($request->filled('status_filter') && $request->input('status_filter') !== 'all') {
                $statusFilter = $request->input('status_filter');
                $meetings = $meetings->filter(function ($meeting) use ($statusFilter) {
                    return $meeting->calculated_status === $statusFilter;
                });
            }

            // 2. Sort Logic
            // Default sort: Status (Scheduled -> Ongoing -> Completed -> Cancelled), then Start Time
            // We define a helper for Status Weight
            $getStatusWeight = function ($status) {
                return match($status) {
                    'scheduled' => 1,
                    'ongoing' => 2,
                    'completed' => 3,
                    'cancelled' => 4,
                    default => 5,
                };
            };

            if ($request->has('sort_by') && in_array($sortBy, ['topic', 'room.name', 'start_time', 'user.name', 'calculated_status'])) {
                $descending = $sortDirection === 'desc';
                
                if ($sortBy === 'calculated_status') {
                    $meetings = $meetings->sortBy(function ($meeting) use ($getStatusWeight) {
                        return $getStatusWeight($meeting->calculated_status);
                    }, SORT_REGULAR, $descending);
                } else {
                    $meetings = $descending 
                        ? $meetings->sortByDesc($sortBy) 
                        : $meetings->sortBy($sortBy);
                }
            } else {
                // Default Sorting: Custom Status Order, then Start Time Ascending
                $meetings = $meetings->sortBy(function ($meeting) use ($getStatusWeight) {
                    // Combine status weight and timestamp for sorting
                    // Multi-level sort: Status first, then Start Time
                    return [$getStatusWeight($meeting->calculated_status), $meeting->start_time->timestamp];
                });
            }

            // Pagination Logic (Manual Pagination because of Collection Sorting/Filtering)
            $perPage = 10;
            $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
            $currentItems = $meetings->slice(($currentPage - 1) * $perPage, $perPage)->all();
            $meetings = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentItems,
                $meetings->count(),
                $perPage,
                $currentPage,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
            );
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
            case 'all':
                $effectiveStartDate = \Carbon\Carbon::create(2000, 1, 1)->startOfDay();
                $effectiveEndDate = \Carbon\Carbon::create(2100, 12, 31)->endOfDay();
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
        if ($meeting->calculated_status === 'cancelled') {
            abort(403, 'Meeting is cancelled.');
        }

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

        // Render View to String
        $html = view('exports.attendance-list', [
            'meeting' => $meeting,
            'participants' => $participants
        ])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', "attachment; filename=\"Daftar_Hadir_{$meeting->topic}.xls\"");
    }
}

