$user = App\Models\User::where('npk', '321321')->first();
if (!$user) {
    echo "User not found.\n";
    exit;
}

echo "User Name: " . $user->name . "\n";
echo "Roles: " . implode(', ', $user->getRoleNames()->toArray()) . "\n";
echo "Permissions via Roles: " . $user->getAllPermissions()->pluck('name')->implode(', ') . "\n";

// Check if user has Resepsionis role
if ($user->hasRole('Resepsionis')) {
    echo "User HAS 'Resepsionis' role.\n";
} else {
    echo "User DOES NOT HAVE 'Resepsionis' role.\n";
    // Attempt to assign it just in case that's the fix needed
    try {
        $user->assignRole('Resepsionis');
        echo "Assigned 'Resepsionis' role.\n";
    } catch (\Exception $e) {
        echo "Failed to assign role: " . $e->getMessage() . "\n";
    }
}
