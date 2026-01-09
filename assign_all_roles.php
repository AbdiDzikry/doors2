$user = App\Models\User::where('npk', '321321')->first();
$roles = Spatie\Permission\Models\Role::all()->pluck('name');

echo "Assigning all roles to user {$user->name}...\n";
foreach ($roles as $role) {
    if (!$user->hasRole($role)) {
        $user->assignRole($role);
        echo "Assigned: $role\n";
    } else {
        echo "Already has: $role\n";
    }
}
echo "Done.\n";
