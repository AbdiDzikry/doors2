<?php

namespace App\Http\Controllers\Meeting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RecurringMeeting;
use App\Models\Meeting;

class RecurringMeetingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $recurringMeetings = RecurringMeeting::all();
        return view('meetings.recurring-meetings.index', compact('recurringMeetings'));
    }

    /**
     * Display the specified resource.
     */
    public function show(RecurringMeeting $recurringMeeting)
    {
        return view('meetings.recurring-meetings.show', compact('recurringMeeting'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RecurringMeeting $recurringMeeting)
    {
        return view('meetings.recurring-meetings.edit', compact('recurringMeeting'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RecurringMeeting $recurringMeeting)
    {
        $validatedData = $request->validate([
            'frequency' => 'required|in:daily,weekly,monthly',
            'ends_at' => 'required|date|after:start_date',
        ]);

        $recurringMeeting->update($validatedData);

        // Logic to update all associated meetings in the series
        // This will be complex and needs careful consideration of existing meetings
        // For now, we'll just update the recurring meeting record itself.

        return redirect()->route('meeting.recurring-meetings.index')
                        ->with('success', 'Recurring meeting updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RecurringMeeting $recurringMeeting)
    {
        // Logic to delete all associated meetings in the series
        // For now, we'll just delete the recurring meeting record itself.
        $recurringMeeting->delete();

        return redirect()->route('meeting.recurring-meetings.index')
                        ->with('success', 'Recurring meeting deleted successfully.');
    }
}
