<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Rap2hpoutre\FastExcel\FastExcel;

class UserController extends Controller
{
    

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('npk', 'like', '%' . $search . '%')
                  ->orWhere('department', 'like', '%' . $search . '%')
                  ->orWhere('division', 'like', '%' . $search . '%')
                  ->orWhere('position', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->role($request->input('role'));
        }

        // Sorting logic
        $sortField = $request->input('sort_by', 'name'); // Default sort by name
        $sortDirection = $request->input('sort_direction', 'asc'); // Default sort direction asc

        // Allow sorting on specific columns
        $allowedSorts = ['name', 'npk', 'division', 'department', 'position', 'email', 'phone'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $users = $query->with('roles')->paginate(10);

        $roles = Role::all(); // For role filter dropdown

        return view('master.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {
        $roles = Role::all();
        return view('master.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|regex:/^[a-zA-Z\s]+$/',
            'email' => 'nullable|email|unique:users',
            'password' => 'required|confirmed',
            'roles' => 'required|array',
            'npk' => 'nullable|string|max:255|unique:users,npk',
            'division' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
        ], [
            'name.regex' => 'Name format is invalid. Only letters and spaces are allowed.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'npk' => $request->npk,
            'division' => $request->division,
            'department' => $request->department,
            'position' => $request->position,
            'phone' => $request->phone,
        ]);

        $user->assignRole($request->roles);

        return redirect()->route('master.users.index')
                        ->with('success','User created successfully.');
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
    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->getRoleNames()->toArray();
        return view('master.users.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|regex:/^[a-zA-Z\s]+$/',
            'email' => 'nullable|email|unique:users,email,'.$user->id,
            'password' => 'nullable|confirmed',
            'roles' => 'required|array',
            'npk' => 'nullable|string|max:255|unique:users,npk,'.$user->id,
            'division' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
        ], [
            'name.regex' => 'Name format is invalid. Only letters and spaces are allowed.',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'npk' => $request->npk,
            'division' => $request->division,
            'department' => $request->department,
            'position' => $request->position,
            'phone' => $request->phone,
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        $user->syncRoles($request->roles);

        return redirect()->route('master.users.index')
                        ->with('success','User updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('master.users.index')
                        ->with('success','User deleted successfully');
    }

    public function downloadTemplate()
    {
        $headings = [
            'FULL NAME',
            'NPK',
            'DIVISION',
            'DEPARTMENT',
            'POSITION',
            'EMAIL',
            'PHONE',
        ];
        // FastExcel does not have a direct way to create a header-only file.
        // We can create a collection containing only the headers and export that.
        $data = collect([$headings]);
        return (new FastExcel($data))->download('users_template.xlsx');
    }

    public function import(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            (new FastExcel)->import($request->file('file'), function ($row) {
                // Check for email existence and that it's not empty
                if (isset($row['EMAIL']) && !empty($row['EMAIL']) && !User::where('email', $row['EMAIL'])->exists()) {
                    $user = User::create([
                        'name'       => $row['FULL NAME'] ?? null,
                        'npk'        => $row['NPK'] ?? null,
                        'division'   => $row['DIVISION'] ?? null,
                        'department' => $row['DEPARTMENT'] ?? null,
                        'position'   => $row['POSITION'] ?? null,
                        'email'      => $row['EMAIL'],
                        'phone'      => $row['PHONE'] ?? null,
                        'password'   => Hash::make('password'), // Default password
                    ]);
                    $user->assignRole('karyawan');
                }
            });
        } catch (\Exception $e) {
            return redirect()->route('master.users.index')->with('error', 'Error importing file: ' . $e->getMessage());
        }

        return redirect()->route('master.users.index')->with('success', 'Users imported successfully!');
    }

    

    public function export()
    {
        $usersExport = new \App\Exports\UsersExport();
        $usersCollection = $usersExport->collection();
        $usersHeadings = $usersExport->headings();

        return (new FastExcel($usersCollection))->download('users.xlsx', function ($user) use ($usersHeadings) {
            $row = [];
            $row[$usersHeadings[0]] = $user->name;
            $row[$usersHeadings[1]] = $user->npk;
            $row[$usersHeadings[2]] = $user->division;
            $row[$usersHeadings[3]] = $user->department;
            $row[$usersHeadings[4]] = $user->position;
            $row[$usersHeadings[5]] = $user->email;
            $row[$usersHeadings[6]] = $user->phone;
            return $row;
        });
    }

    public function syncFromApi(\App\Services\EmployeeApiService $apiService)
    {
        $result = $apiService->syncAll(force: true);

        if ($result['status'] === 'success') {
            return redirect()->route('master.users.index')
                ->with('success', "Sync completed. Synced: {$result['synced']}, Errors: {$result['errors']}");
        }

        return redirect()->route('master.users.index')
            ->with('error', 'Sync failed. Please check logs.');
    }
}
