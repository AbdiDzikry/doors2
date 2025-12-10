<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use App\Exports\UsersTemplateExport;
use App\Imports\UsersImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Collection;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Karyawan']);
        Role::create(['name' => 'Resepsionis']);
        Role::create(['name' => 'Manager']);

        // Create an admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');

        $this->withoutExceptionHandling();




    }

    /** @test */
    public function admin_can_view_users_index()
    {
        $this->actingAs($this->admin)
             ->get(route('master.users.index'))
             ->assertStatus(200)
             ->assertViewIs('master.users.index');
    }

    /** @test */
    public function admin_can_view_create_user_page()
    {
        $this->actingAs($this->admin)
             ->get(route('master.users.create'))
             ->assertStatus(200)
             ->assertViewIs('master.users.create')
             ->assertViewHas('roles');
    }

    /** @test */
    public function admin_can_store_a_new_user()
    {
        $role = Role::where('name', 'Karyawan')->first();

        $this->actingAs($this->admin)
             ->post(route('master.users.store'), [
                 'name' => 'Test User',
                 'email' => 'test@example.com',
                 'password' => 'password',
                 'password_confirmation' => 'password',
                 'roles' => [$role->name],
             ])
             ->assertRedirect(route('master.users.index'))
             ->assertSessionHas('success', 'User created successfully.');

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->hasRole('Karyawan'));
    }

    /** @test */
    public function admin_can_view_edit_user_page()
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)
             ->get(route('master.users.edit', $user))
             ->assertStatus(200)
             ->assertViewIs('master.users.edit')
             ->assertViewHasAll(['user', 'roles', 'userRoles']);
    }

    /** @test */
    public function admin_can_update_a_user()
    {
        $user = User::factory()->create();
        $user->assignRole('Karyawan');

        $newRole = Role::where('name', 'Manager')->first();

        $this->actingAs($this->admin)
             ->put(route('master.users.update', $user), [
                 'name' => 'Updated User',
                 'email' => 'updated@example.com',
                 'password' => 'newpassword',
                 'password_confirmation' => 'newpassword',
                 'roles' => [$newRole->name],
             ])
             ->assertRedirect(route('master.users.index'))
             ->assertSessionHas('success', 'User updated successfully');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
            'email' => 'updated@example.com',
        ]);

        $updatedUser = User::find($user->id);
        $this->assertTrue(Hash::check('newpassword', $updatedUser->password));
        $this->assertTrue($updatedUser->hasRole('Manager'));
        $this->assertFalse($updatedUser->hasRole('Karyawan'));
    }

    /** @test */
    public function admin_can_delete_a_user()
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)
             ->delete(route('master.users.destroy', $user))
             ->assertRedirect(route('master.users.index'))
             ->assertSessionHas('success', 'User deleted successfully');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /** @test */
    public function user_creation_requires_validation()
    {
        try {
            $this->actingAs($this->admin)
                 ->post(route('master.users.store'), []);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertEquals([
                'name' => ['The name field is required.'],
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.'],
                'roles' => ['The roles field is required.'],
            ], $e->errors());
            return;
        }

        $this->fail('ValidationException was not thrown.');
    }

    /** @test */
    public function user_update_requires_validation()
    {
        $user = User::factory()->create();

        try {
            $this->actingAs($this->admin)
                 ->put(route('master.users.update', $user), [
                     'name' => '',
                     'email' => 'invalid-email',
                     'password' => 'password',
                     'password_confirmation' => 'wrong_password',
                     'roles' => [],
                 ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertEquals([
                'name' => ['The name field is required.'],
                'email' => ['The email field must be a valid email address.'],
                'password' => ['The password field confirmation does not match.'],
                'roles' => ['The roles field is required.'],
            ], $e->errors());
            return;
        }

        $this->fail('ValidationException was not thrown.');
    }

    // /** @test */
    // public function admin_can_download_users_template()
    // {
    //     $response = $this->actingAs($this->admin)
    //          ->get(route('master.users.template'));

    //     $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //     $response->assertHeader('Content-Disposition', 'attachment; filename=users_template.xlsx');
    // }

    // /** @test */
    // public function admin_can_import_users()
    // {
    //     // Create a temporary directory for the test file
    //     $tempDir = sys_get_temp_dir() . '/excel_test';
    //     if (!File::exists($tempDir)) {
    //         File::makeDirectory($tempDir);
    //     }

    //     $filePath = $tempDir . '/users.csv';
    //     $csvContent = "FULL NAME,NPK,DIVISION,DEPARTMENT,POSITION,EMAIL,PHONE\nImported User,98765,HR,Recruitment,Specialist,imported.user@example.com,987654321";
    //     File::put($filePath, $csvContent);

    //     $file = UploadedFile::fake()->create(
    //         'users.csv',
    //         File::size($filePath),
    //         'text/csv'
    //     );

    //     // Manually set the path for the fake uploaded file to the real temporary file
    //     $file->tempFile = $filePath;

    //     $this->actingAs($this->admin)
    //          ->post(route('master.users.import'), [
    //              'file' => $file,
    //          ])
    //          ->assertRedirect(route('master.users.index'))
    //          ->assertSessionHas('success', 'Users imported successfully!');

    //     Excel::assertImported('users.csv', function(UsersImport $import) {
    //         return true;
    //     });

    //     $this->assertDatabaseHas('users', [
    //         'name' => 'Imported User',
    //         'email' => 'imported.user@example.com',
    //     ]);

    //     // Clean up the created file and directory
    //     File::delete($filePath);
    //     File::deleteDirectory($tempDir);
    // }



    // /** @test */
    // public function admin_can_export_users()
    // {
    //     $response = $this->actingAs($this->admin)
    //          ->get(route('master.users.export'));

    //     $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //     $response->assertHeader('Content-Disposition', 'attachment; filename=users.xlsx');
    // }

    /** @test */
    // /** @test */
    // public function admin_can_download_test_file()
    // {
    //     $response = $this->actingAs($this->admin)
    //          ->get(route('master.test.download'));

    //     $response->assertStatus(200);
    //     $response->assertHeader('Content-Type', 'text/plain');
    //     $response->assertHeader('Content-Disposition', 'attachment; filename="test.txt"');
    // }
}
