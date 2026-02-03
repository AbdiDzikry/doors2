<?php

namespace App\Http\Controllers\GeneralAffair;

use App\Http\Controllers\Controller;
use App\Models\GeneralAffair\GaAcTicket;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Status Tabs Logic
        $statusGroup = $request->input('tab', 'new'); // new, process, history

        $query = GaAcTicket::with(['asset', 'reporter']); // Assuming reporter relationship exists or is planned

        switch ($statusGroup) {
            case 'new':
                $query->where('status', 'pending_validation');
                break;
            case 'process':
                $query->whereIn('status', ['open', 'assigned', 'in_progress']);
                break;
            case 'history':
                $query->whereIn('status', ['resolved', 'closed', 'false_alarm']);
                break;
        }

        $tickets = $query->latest()->paginate(20);

        // Counts for tabs
        $countNew = GaAcTicket::where('status', 'pending_validation')->count();
        $countProcess = GaAcTicket::whereIn('status', ['open', 'assigned', 'in_progress'])->count();
        $countHistory = GaAcTicket::whereIn('status', ['resolved', 'closed', 'false_alarm'])->count();

        return view('ga.tickets.index', compact('tickets', 'statusGroup', 'countNew', 'countProcess', 'countHistory'));
    }

    /**
     * Display the specified resource.
     */
    public function show($uuid)
    {
        $ticket = GaAcTicket::where('uuid', $uuid)->with(['asset', 'technician', 'validator'])->firstOrFail();
        $technicians = \App\Models\User::all(); // In real app, filter by role 'technician'
        return view('ga.tickets.show', compact('ticket', 'technicians'));
    }

    public function validateTicket($uuid)
    {
        $ticket = GaAcTicket::where('uuid', $uuid)->firstOrFail();
        $ticket->update([
            'status' => 'open',
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);

        return back()->with('success', 'Tiket berhasil divalidasi.');
    }

    public function rejectTicket(Request $request, $uuid)
    {
        $ticket = GaAcTicket::where('uuid', $uuid)->firstOrFail();
        // Maybe add rejection reason? Using 'description' or new field? defaults to false_alarm for now.
        $ticket->update([
            'status' => 'false_alarm',
            'validated_by' => auth()->id(),
            'validated_at' => now(),
            // 'resolution_notes' => $request->reason // Optional
        ]);

        return back()->with('success', 'Tiket ditolak.');
    }

    public function assignTicket(Request $request, $uuid)
    {
        $ticket = GaAcTicket::where('uuid', $uuid)->firstOrFail();
        $request->validate(['technician_id' => 'required|exists:users,id']);

        $ticket->update([
            'status' => 'assigned',
            'technician_id' => $request->technician_id,
        ]);

        return back()->with('success', 'Teknisi berhasil ditugaskan.');
    }

    public function resolveTicket(Request $request, $uuid)
    {
        $ticket = GaAcTicket::where('uuid', $uuid)->firstOrFail();
        $request->validate([
            'resolution_notes' => 'required|string',
            'repair_cost' => 'nullable|numeric'
        ]);

        $ticket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolution_notes' => $request->resolution_notes,
            'repair_cost' => $request->repair_cost ?? 0,
        ]);

        return back()->with('success', 'Tiket berhasil diselesaikan.');
    }
}
