<?php

namespace App\Http\Controllers\GeneralAffair;

use App\Http\Controllers\Controller;
use App\Models\GeneralAffair\GaAcAsset;
use App\Models\GeneralAffair\GaAcTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicReportController extends Controller
{
    /**
     * Show the ticket report form for a specific asset.
     *
     * @param  string  $uuid
     * @return \Illuminate\View\View
     */
    public function show($uuid)
    {
        $asset = GaAcAsset::where('uuid', $uuid)->firstOrFail();

        return view('ga.public.report', compact('asset'));
    }

    /**
     * Store a new ticket report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $uuid
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $uuid)
    {
        $asset = GaAcAsset::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'reporter_name' => 'required|string|max:255',
            'reporter_nik' => 'required|string|max:50',
            'issue_category' => 'required|in:not_cold,leaking,noisy,dead,smell,other',
            'description' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($validated, $asset) {
            GaAcTicket::create([
                'ga_ac_asset_id' => $asset->id,
                'reporter_name' => $validated['reporter_name'],
                'reporter_nik' => $validated['reporter_nik'],
                'issue_category' => $validated['issue_category'],
                'description' => $validated['description'],
                'status' => 'pending_validation',
            ]);

            // Optional: Update asset status to 'needs_repair' immediately?
            // Or wait for validation? Let's wait for validation to prevent abuse.
        });

        return redirect()->route('ga.report.show', $uuid)
            ->with('success', 'Laporan berhasil dikirim! Tim GA akan segera memverifikasi.');
    }
}
