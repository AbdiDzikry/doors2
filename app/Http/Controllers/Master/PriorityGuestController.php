<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PriorityGuest;

class PriorityGuestController extends Controller
{

    public function index()
    {
        $guests = PriorityGuest::paginate(10);
        return view('master.priority.index', compact('guests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master.priority.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'level' => 'required|integer|min:1|max:5',
        ]);

        PriorityGuest::create($request->all());

        return redirect()->route('master.priority-guests.index')
                        ->with('success','Priority Guest created successfully.');
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
    public function edit(PriorityGuest $priorityGuest)
    {
        return view('master.priority.edit',['guest' => $priorityGuest]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PriorityGuest $priorityGuest)
    {
        $request->validate([
            'name' => 'required',
            'level' => 'required|integer|min:1|max:5',
        ]);

        $priorityGuest->update($request->all());

        return redirect()->route('master.priority-guests.index')
                        ->with('success','Priority Guest updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PriorityGuest $priorityGuest)
    {
        $priorityGuest->delete();

        return redirect()->route('master.priority-guests.index')
                        ->with('success','Priority Guest deleted successfully');
    }
}