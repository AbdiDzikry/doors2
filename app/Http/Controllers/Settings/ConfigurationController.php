<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Configuration;

class ConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Configuration::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('key', 'like', '%' . $search . '%');
        }

        $configurations = $query->paginate(10);
        return view('settings.configuration.index', compact('configurations'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('settings.configuration.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|unique:configurations',
            'value' => 'required',
            'description' => 'nullable|string',
        ]);

        Configuration::create($request->all());

        return redirect()->route('settings.configurations.index')
            ->with('success', 'Configuration created successfully.');
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
    public function edit(Configuration $configuration)
    {
        return view('settings.configuration.edit', ['config' => $configuration]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Configuration $configuration)
    {
        $request->validate([
            'value' => 'required',
            'description' => 'nullable|string',
        ]);

        $configuration->update($request->all());

        return redirect()->route('settings.configurations.index')
            ->with('success', 'Configuration updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Configuration $configuration)
    {
        $configuration->delete();

        return redirect()->route('settings.configurations.index')
            ->with('success', 'Configuration deleted successfully');
    }

    /**
     * Update multiple configurations at once
     */
    public function updateBulk(Request $request)
    {
        $configurations = $request->input('configurations', []);

        foreach ($configurations as $key => $value) {
            Configuration::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Set unchecked toggles to 0
        $allToggleKeys = ['auto_cancel_unattended_meetings', 'enable_feature_tour'];
        foreach ($allToggleKeys as $toggleKey) {
            if (!isset($configurations[$toggleKey])) {
                Configuration::updateOrCreate(
                    ['key' => $toggleKey],
                    ['value' => '0']
                );
            }
        }

        return redirect()->route('settings.configurations.index')
            ->with('success', 'Settings updated successfully');
    }
}
