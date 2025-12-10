<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PantryItem;

class PantryItemController extends Controller
{
    public function index()
    {
        $items = PantryItem::paginate(10);
        return view('master.pantry.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('master.pantry.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:makanan,minuman',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validatedData['image'] = $request->file('image')->store('pantry-items', 'public');
        }

        PantryItem::create($validatedData);

        return redirect()->route('master.pantry-items.index')
                        ->with('message','Item created successfully.');
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
    public function edit(PantryItem $pantryItem)
    {
        return view('master.pantry.edit',['item' => $pantryItem]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PantryItem $pantryItem)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:makanan,minuman',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($pantryItem->image) {
                Storage::disk('public')->delete($pantryItem->image);
            }
            $validatedData['image'] = $request->file('image')->store('pantry-items', 'public');
        }

        $pantryItem->update($validatedData);

        return redirect()->route('master.pantry-items.index')
                        ->with('message','Item updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PantryItem $pantryItem)
    {
        $pantryItem->delete();

        return redirect()->route('master.pantry-items.index')
                        ->with('success','Item deleted successfully');
    }
}