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

        // Fetch Sync Status
        $syncStatus = \App\Models\Configuration::where('key', 'employee_sync_status')->value('value');
        $syncLastRun = \App\Models\Configuration::where('key', 'employee_sync_last_run')->value('value');

        return view('master.users.index', compact('users', 'roles', 'syncStatus', 'syncLastRun'));
    }

    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {
        $roles = Role::all();
        
        // Fetch specific data for dropdowns
        $divisions = User::whereNotNull('division')->distinct()->orderBy('division')->pluck('division');
        $departments = User::whereNotNull('department')->distinct()->orderBy('department')->pluck('department');
        $positions = User::whereNotNull('position')->distinct()->orderBy('position')->pluck('position');

        return view('master.users.create', compact('roles', 'divisions', 'departments', 'positions'));
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

    public function downloadContactTemplate()
    {
        $headings = [
            'No',
            'Employee No.',
            'Employee Name',
            'email',
            'Phone',
        ];
        // Export just the headers
        $data = collect([$headings]);
        return (new FastExcel($data))->download('update_contacts_template.xlsx');
    }

    public function importContacts(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            $count = 0;
            $updated = 0;
            
            // Debugging: Log the start of import
            \Illuminate\Support\Facades\Log::info('Starting Contact Import');

            (new FastExcel)->import($request->file('file'), function ($row) use (&$count, &$updated) {
                // Debugging: Log the raw row data
                \Illuminate\Support\Facades\Log::info('Import Row: ' . json_encode($row));
                
                // Determine values based on keys OR generic indices
                // Template: [0] No, [1] Employee No., [2] Employee Name, [3] email, [4] Phone
                
                $npk = $row['Employee No.'] ?? $row['employee no.'] ?? $row[1] ?? null;
                $email = $row['email'] ?? $row['Email'] ?? $row[3] ?? null;
                $phone = $row['Phone'] ?? $row['phone'] ?? $row[4] ?? null;

                // 1. Skip if no NPK
                if (!$npk) {
                    return;
                }
                
                // 2. Skip if this is actually the header row being read as data
                if (strcasecmp($npk, 'Employee No.') === 0 || strcasecmp($npk, 'NPK') === 0) {
                    return;
                }

                // Normalize Phone Number: 62 -> 0
                if ($phone) {
                    $phoneStr = (string)$phone;
                    // Trim spaces
                    $phoneStr = trim($phoneStr);               
                    if (str_starts_with($phoneStr, '62')) {
                        $phone = '0' . substr($phoneStr, 2);
                    }
                }

                $user = User::where('npk', $npk)->first();

                if ($user) {
                    $updateData = [];
                    // Only update if the file has data (allow null/empty to NOT overwrite existing data? OR overwrite with empty?)
                    // Typically "Update" means if you provide value, we use it. If empty in excel, maybe keep existing?
                    // Code below only updates if !empty.  
                    if (!empty($email)) {
                        // Check if email belongs to another user
                        $emailOwner = User::where('email', $email)->where('id', '!=', $user->id)->first();
                        if ($emailOwner) {
                            \Illuminate\Support\Facades\Log::warning("Duplicate Email Skipped: {$email} is already used by {$emailOwner->name} (NPK: {$emailOwner->npk}). Keeping original for {$user->name}.");
                        } else {
                            $updateData['email'] = $email;
                        }
                    }
                    
                    if (!empty($phone)) $updateData['phone'] = $phone;

                    if (!empty($updateData)) {
                        $user->update($updateData);
                        $updated++;
                        \Illuminate\Support\Facades\Log::info("Updated User: {$user->name} (NPK: {$npk})");
                    } else {
                        \Illuminate\Support\Facades\Log::info("No changes for User: {$user->name} (NPK: {$npk})");
                    }
                } else {
                    \Illuminate\Support\Facades\Log::warning("User not found for NPK: {$npk}");
                }
                $count++;
            });

            return redirect()->route('master.users.index')->with('success', "Contact info updated. {$updated} users updated.");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Import Error: ' . $e->getMessage());
            return redirect()->route('master.users.index')->with('error', 'Error importing contact file: ' . $e->getMessage());
        }
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
