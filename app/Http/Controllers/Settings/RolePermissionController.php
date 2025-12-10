<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar; // Import PermissionRegistrar

class RolePermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();
        $permissions = Permission::all();
        return view('settings.role-permissions.index', compact('roles', 'permissions'));
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
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->input('permissions'));

        // Clear permission cache after creating a role
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('settings.role-permissions.index')
                        ->with('success','Role created successfully.');
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
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('settings.role-permissions.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update(['name' => $request->name]);

        $permissions = Permission::whereIn('id', $request->input('permissions', []))->pluck('name')->toArray();
        $role->syncPermissions($permissions);

        // Clear permission cache after updating a role
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('settings.role-permissions.index')
                        ->with('success','Permissions updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        // Clear permission cache after deleting a role
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->route('settings.role-permissions.index')
                        ->with('success','Role deleted successfully.');
    }
}
