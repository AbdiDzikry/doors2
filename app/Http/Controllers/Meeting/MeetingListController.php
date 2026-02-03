<?php

namespace App\Http\Controllers\Meeting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meeting;

use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

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

        // Store current URL with query parameters for "Back" functionality
        session(['meeting_list_url' => $request->fullUrl()]);

        $activeTab = $request->input('tab', 'meeting-list');
        $filter = $request->input('filter', 'day');
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');

        if ($startDateInput || $endDateInput) {
            $filter = 'custom';
        }

        // Calculate dates
        [$effectiveStartDate, $effectiveEndDate] = $this->calculateDateRange($filter, $startDateInput, $endDateInput);

        // Initialize variables
        $meetings = collect();
        $myMeetings = collect();
        $stats = [];
        $sortBy = $request->input('sort_by', 'start_time');
        $sortDirection = $request->input('sort_direction', 'asc');

        if ($activeTab === 'my-meetings') {
            $myMeetings = $this->getMyMeetings($user, $effectiveStartDate, $effectiveEndDate);

            // Stats Calculation for My Meetings
            $baseQuery = $this->getMyMeetingsQuery($user, $effectiveStartDate, $effectiveEndDate);
            $stats = $this->calculateStats($baseQuery);
        } else {
            $meetings = $this->getAllMeetings($request, $effectiveStartDate, $effectiveEndDate);
        }

        return view('meetings.list.index', compact('meetings', 'myMeetings', 'stats', 'filter', 'effectiveStartDate', 'effectiveEndDate', 'activeTab', 'sortBy', 'sortDirection'));
    }

    private function getMyMeetingsQuery($user, $start, $end)
    {
        return Meeting::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhereHas('meetingParticipants', function ($subQ) use ($user) {
                    $subQ->where('participant_type', \App\Models\User::class)
                        ->where('participant_id', $user->id);
                });
        })
            ->where('status', '!=', 'cancelled')
            ->whereBetween('start_time', [$start, $end]);
    }

    private function getMyMeetings($user, $start, $end)
    {
        return $this->getMyMeetingsQuery($user, $start, $end)
            ->with('room')
            ->orderBy('start_time', 'desc')
            ->paginate(10)
            ->withQueryString();
    }

    private function calculateStats($query)
    {
        $now = now();
        return [
            'total' => (clone $query)->count(),
            'scheduled' => (clone $query)->where('start_time', '>', $now)->count(),
            'ongoing' => (clone $query)->where('start_time', '<=', $now)->where('end_time', '>=', $now)->count(),
            'completed' => (clone $query)->where('end_time', '<', $now)->count(),
            'cancelled' => 0
        ];
    }

    private function getAllMeetings(Request $request, $start, $end)
    {
        $query = Meeting::query()->where('status', '!=', 'cancelled');
        $query->whereBetween('start_time', [$start, $end]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('topic', 'like', '%' . $search . '%')
                    ->orWhereHas('room', function ($qr) use ($search) {
                        $qr->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('user', function ($qu) use ($search) {
                        $qu->where('name', 'like', '%' . $search . '%')
                            ->orWhere('npk', 'like', '%' . $search . '%');
                    });
            });
        }

        $meetings = $query->with('room', 'user')->get();

        // 1. Filter by Status
        if ($request->filled('status_filter') && $request->input('status_filter') !== 'all') {
            $statusFilter = $request->input('status_filter');
            $meetings = $meetings->filter(function ($meeting) use ($statusFilter) {
                return $meeting->calculated_status === $statusFilter;
            });
        }

        // 2. Sort
        $sortBy = $request->input('sort_by', 'start_time');
        $sortDirection = $request->input('sort_direction', 'asc');
        $meetings = $this->sortMeetings($meetings, $sortBy, $sortDirection);

        // 3. Paginate
        return $this->paginateCollection($meetings, 10, $request);
    }

    private function sortMeetings($meetings, $sortBy, $sortDirection)
    {
        $getStatusWeight = function ($status) {
            return match ($status) {
                'scheduled' => 1,
                'ongoing' => 2,
                'completed' => 3,
                'cancelled' => 4,
                default => 5,
            };
        };

        if ($sortBy && in_array($sortBy, ['topic', 'room.name', 'start_time', 'user.name', 'calculated_status'])) {
            $descending = $sortDirection === 'desc';

            if ($sortBy === 'calculated_status') {
                return $meetings->sortBy(function ($meeting) use ($getStatusWeight) {
                    return $getStatusWeight($meeting->calculated_status);
                }, SORT_REGULAR, $descending);
            } else {
                return $descending
                    ? $meetings->sortByDesc($sortBy)
                    : $meetings->sortBy($sortBy);
            }
        }

        // Default Sort
        return $meetings->sortBy(function ($meeting) use ($getStatusWeight) {
            return [$getStatusWeight($meeting->calculated_status), $meeting->start_time->timestamp];
        });
    }

    private function paginateCollection($items, $perPage, $request)
    {
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $currentItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->all();
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );
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
    public function update(Request $request, Meeting $meeting, \App\Services\BookingService $bookingService)
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

        // Notify participants of the update
        // Note: Participants are not synced here, only topic/time.
        // If participant sync is needed, we should use BookingService::updateMeeting fully.
        // For now, we just notify the existing list.
        try {
            // Need to reload relations to ensure participants are loaded for email
            $meeting->load('meetingParticipants.participant', 'user');
            // We can access sendMeetingInvitation via reflection or make it public.
            // Wait, sendMeetingInvitation is protected in BookingService. 
            // Better to add a public method triggerUpdateNotification or allow access.
            // Actually, in previous step I made duplication of sendMeetingInvitation protected? 
            // In the "Refactor BookingService" step, I removed the old one. The new one is protected?
            // "protected function sendMeetingInvitation"
            // I should make it PUBLIC if I want to call it from here.
            // Or I can call updateMeeting? But arguments mismatch.
            // Let's make sendMeetingInvitation PUBLIC in BookingService.
            $bookingService->sendMeetingInvitation($meeting, 'update');
        } catch (\Exception $e) {
            \Log::error("Failed to send update notifications: " . $e->getMessage());
        }

        return redirect()->route('meeting.meeting-lists.show', $meeting)->with('success', 'Meeting updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Meeting $meeting, \App\Services\BookingService $bookingService)
    {
        // Authorization: Only the meeting creator or an admin can cancel.
        if (auth()->user()->id !== $meeting->user_id && !auth()->user()->hasRole(['Super Admin', 'Admin'])) {
            return back()->with('error', 'You are not authorized to cancel this meeting.');
        }

        $bookingService->cancelMeeting($meeting);

        return back()->with('success', 'Meeting cancelled successfully and participants notified.');
    }

    public function exportAttendance(Meeting $meeting)
    {
        if ($meeting->calculated_status === 'cancelled') {
            abort(403, 'Meeting is cancelled.');
        }

        // Authorization: Removed restricted ownership/participant check to allow all authenticated users
        // as per user request. Keep check for cancelled meeting if needed.

        $participants = $meeting->meetingParticipants()->with('participant')->get();

        // Render View to String
        $html = view('exports.attendance-list', [
            'meeting' => $meeting,
            'participants' => $participants
        ])->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', "attachment; filename=\"Daftar_Hadir_{$meeting->topic}.xls\"");
    }

    public function exportAttendancePdf(Meeting $meeting)
    {
        if ($meeting->calculated_status === 'cancelled') {
            abort(403, 'Meeting is cancelled.');
        }

        // Authorization: Removed restricted ownership/participant check to allow all authenticated users
        // as per user request.

        $participants = $meeting->meetingParticipants()->with('participant')->get();

        // Load data
        $data = [
            'meeting' => $meeting,
            'participants' => $participants
        ];

        // Generate PDF
        $pdf = Pdf::loadView('exports.attendance-pdf', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream("Daftar_Hadir_{$meeting->topic}.pdf");
    }
}

