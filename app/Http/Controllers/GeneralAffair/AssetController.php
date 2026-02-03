<?php

namespace App\Http\Controllers\GeneralAffair;

use App\Http\Controllers\Controller;
use App\Models\GeneralAffair\GaAcAsset;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = GaAcAsset::query();

        // Filters
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->has('location') && $request->location != '') {
            $query->where('location', $request->location);
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Data for Filters
        $locations = GaAcAsset::select('location')->distinct()->orderBy('location')->pluck('location');
        $statuses = ['good', 'needs_repair', 'broken', 'disposed', 'maintenance']; // Added maintenance based on UI

        // View Mode Logic
        $viewMode = $request->input('view', 'visual'); // Default to visual as per request

        if ($viewMode === 'visual') {
            // Get all assets needed for visual mode (grouped by location)
            $assets = $query->orderBy('location')->orderBy('sku')->get()->groupBy('location');
            $totalAssets = $query->count(); // Use count from query for accuracy
        } else {
            // Paginate for list mode
            $assets = $query->orderBy('location')->orderBy('sku')->paginate(20);
            $totalAssets = $assets->total();
        }

        return view('ga.assets.index', compact('assets', 'locations', 'statuses', 'viewMode', 'totalAssets'));
    }

    /**
     * Show the QR Code for printing.
     */
    public function printQr($uuid)
    {
        $asset = GaAcAsset::where('uuid', $uuid)->firstOrFail();

        // QR Code URL (Public Report URL)
        $url = route('ga.report.show', $asset->uuid);

        // We will use a Google Chart API or similar in the view
        return view('ga.assets.qr', compact('asset', 'url'));
    }
}
