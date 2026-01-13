<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PantryOrder;
use App\Events\PantryOrderStatusUpdated;
use App\Models\Meeting;

class ReceptionistDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|before_or_equal:today',
        ]);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $request->validate([
                'start_date' => 'before_or_equal:end_date',
            ]);
        }

        // Fetch active (pending or preparing) pantry orders for today's meetings
        // Filter: only meetings with status != 'cancelled'
        $activePantryOrdersGroupedByMeeting = PantryOrder::whereIn('pantry_orders.status', ['pending', 'preparing'])
                                    ->with(['meeting.room', 'meeting.user', 'pantryItem'])
                                    ->join('meetings', 'pantry_orders.meeting_id', '=', 'meetings.id')
                                    ->where('meetings.status', '!=', 'cancelled') // Exclude cancelled
                                    ->whereDate('meetings.start_time', today())
                                    ->orderBy('meetings.start_time')
                                    ->select('pantry_orders.*')
                                    ->get()
                                    ->groupBy('meeting_id');

        // Fetch historical meetings that have pantry orders, with filters
        // Base Query: Meetings that have at least one pantry order (checking status if needed, but history usually implies any status)
        $historicalMeetingsQuery = Meeting::whereHas('pantryOrders')
                                        ->where('status', '!=', 'cancelled') // Exclude cancelled meetings
                                        ->with(['room', 'user', 'pantryOrders.pantryItem']);

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = \Carbon\Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = \Carbon\Carbon::parse($request->input('end_date'))->endOfDay();
            $historicalMeetingsQuery->whereBetween('start_time', [$startDate, $endDate]);
        }

        // Search by meeting topic or pantry item name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $historicalMeetingsQuery->where(function ($q) use ($search) {
                $q->where('topic', 'like', '%' . $search . '%')
                  ->orWhereHas('pantryOrders.pantryItem', function ($q2) use ($search) {
                      $q2->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Apply Status Filter (Check if meeting has ANY order with this status)
        $statusFilter = $request->input('status_filter', 'delivered'); // Default to delivered
        if ($statusFilter !== 'all') {
            $historicalMeetingsQuery->whereHas('pantryOrders', function($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            });
            // Also constrain the eager load if we only want to see matching orders? 
            // Usually history shows the meeting context. Let's filter the orders displayed in view or constrain eager load.
            // For now, let's load all orders for that meeting so the receptionist sees the full context, 
            // OR strictly show only matching orders.
            // Decision: Eager load only matching orders if filter is active, otherwise load all.
            $historicalMeetingsQuery->with(['pantryOrders' => function($q) use ($statusFilter) {
                 $q->where('status', $statusFilter);
            }]);
        }

        $historicalMeetings = $historicalMeetingsQuery->orderBy('start_time', 'desc')->paginate(10);

        $pendingPantryOrdersCount = PantryOrder::where('status', 'pending')->count();
        $todaysMeetings = \App\Models\Meeting::whereDate('start_time', today())->orderBy('start_time')->get();

        return view('dashboards.receptionist', compact('activePantryOrdersGroupedByMeeting', 'historicalMeetings', 'pendingPantryOrdersCount', 'todaysMeetings'));
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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PantryOrder $pantryOrder)
    {
        $request->validate([
            'status' => 'required|in:preparing,delivered',
        ]);

        $pantryOrder->update(['status' => $request->input('status')]);

        // You may want to fetch updated pending orders or broadcast a more specific change
        event(new PantryOrderStatusUpdated($pantryOrder));

        return redirect()->route('dashboard.receptionist')
                        ->with('success','Pantry order status updated successfully.');
    }

    public function updatePantryForMeeting(Request $request, Meeting $meeting)
    {
        $request->validate([
            'status' => 'required|in:preparing,delivered',
        ]);

        $newStatus = $request->input('status');
        
        // Define eligible previous statuses for the transition
        $eligibleStatuses = [];
        if ($newStatus === 'preparing') {
            $eligibleStatuses = ['pending'];
        } elseif ($newStatus === 'delivered') {
            $eligibleStatuses = ['pending', 'preparing']; // Allow delivery from pending or preparing
        } else {
             return redirect()->route('dashboard.receptionist')
                            ->with('error', 'Invalid status update.');
        }

        $count = $meeting->pantryOrders()
                ->whereIn('status', $eligibleStatuses)
                ->update(['status' => $newStatus]);
        
        $message = $count > 0 
            ? "Successfully moved {$count} items to '{$newStatus}'." 
            : "No eligible items found to mark as '{$newStatus}'.";

        return redirect()->route('dashboard.receptionist')
                        ->with('success', $message);
    }

    public function getPantryOrdersPartial()
    {
        $pantryOrders = PantryOrder::where('status', 'pending')
                                    ->with(['meeting.room', 'pantryItem'])
                                    ->join('meetings', 'pantry_orders.meeting_id', '=', 'meetings.id')
                                    ->orderBy('meetings.start_time')
                                    ->select('pantry_orders.*')
                                    ->get();

        return view('dashboards.partials.pantry-orders', compact('pantryOrders'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
