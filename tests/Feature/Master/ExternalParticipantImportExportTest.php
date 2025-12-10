<?php

namespace Tests\Feature\Master;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use App\Models\ExternalParticipant;

class ExternalParticipantImportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->artisan('db:seed --class=RolesAndPermissionsSeeder');

        // Create an Admin user
        $adminRole = Role::findByName('Admin');
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        $this->actingAs($this->admin);
    }

    #[test]
    public function can_download_external_participants_template()
    {
        $response = $this->get(route('master.external-participants.template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename=external_participants_template.csv');

        $content = $response->streamedContent();
        $this->assertEquals("NAME,EMAIL,PHONE,COMPANY,DEPARTMENT,ADDRESS\r\n", $content);
    }

    #[test]
    public function can_export_external_participants()
    {
        ExternalParticipant::factory()->count(5)->create();

        Excel::fake();

        $this->get(route('master.external-participants.export'));

        Excel::assertDownloaded('external_participants.xlsx', function ($export) {
            return $export->collection()->count() === 5;
        });
    }

    #[test]
    public function can_import_external_participants()
    {
        $file = new UploadedFile(
            base_path('tests/Feature/Master/test_import.csv'),
            'test_import.csv',
            'text/csv',
            null,
            true
        );

        $this->post(route('master.external-participants.import'), [
            'file' => $file,
        ]);

        $this->assertDatabaseHas('external_participants', [
            'email' => 'test1@example.com',
        ]);

        $this->assertDatabaseHas('external_participants', [
            'email' => 'test2@example.com',
        ]);
    }

    #[test]
    public function import_handles_extra_columns()
    {
        $file = new UploadedFile(
            base_path('tests/Feature/Master/test_import_extra_columns.csv'),
            'test_import_extra_columns.csv',
            'text/csv',
            null,
            true
        );

        $this->post(route('master.external-participants.import'), [
            'file' => $file,
        ]);

        $this->assertDatabaseHas('external_participants', [
            'email' => 'test1@example.com',
        ]);
        $this->assertDatabaseCount('external_participants', 1);
    }

    #[test]
    public function import_fails_with_missing_required_columns()
    {
        $file = new UploadedFile(
            base_path('tests/Feature/Master/test_import_missing_columns.csv'),
            'test_import_missing_columns.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->post(route('master.external-participants.import'), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors();
    }
}
