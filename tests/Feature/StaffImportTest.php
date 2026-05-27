<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Exports\StaffImportFormatExport;
use App\Imports\StaffImport;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class StaffImportTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('project_user');
        Schema::dropIfExists('user_categories');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('username')->nullable()->unique();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('password')->nullable();
            $table->string('contactNo')->nullable();
            $table->string('address')->nullable();
            $table->integer('role')->default(UserRole::SITE_ENGINEER->value);
            $table->unsignedBigInteger('category')->nullable();
            $table->string('department')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->unsignedBigInteger('vertical_head_id')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_name')->nullable();
            $table->integer('project_type')->default(1);
            $table->timestamps();
        });

        Schema::create('project_user', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('role')->nullable();
            $table->timestamps();
        });

        Schema::create('user_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_code')->nullable();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('module', 64);
            $table->string('action', 64);
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('description', 255)->nullable();
            $table->json('changes')->nullable();
            $table->json('extra')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('request_id', 100)->nullable();
            $table->string('batch_id', 100)->nullable();
            $table->timestamps();
        });

        $this->admin = User::create([
            'name' => 'Admin User',
            'firstName' => 'Admin',
            'lastName' => 'User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => UserRole::ADMIN->value,
        ]);
    }

    public function test_staff_import_format_download_contains_required_columns(): void
    {
        $response = $this->actingAs($this->admin)->get(route('staff.importFormat'));

        $response->assertOk();
        $this->assertStringContainsString(
            'spreadsheetml.sheet',
            $response->headers->get('content-type')
        );
        $this->assertStringContainsString(
            'staff_import_format_',
            $response->headers->get('content-disposition')
        );
        $this->assertStringContainsString(
            '.xlsx',
            $response->headers->get('content-disposition')
        );
    }

    public function test_staff_import_upload_creates_user_from_xlsx_template(): void
    {
        DB::table('projects')->insert([
            'id' => 10,
            'project_name' => 'Streetlight Project',
            'project_type' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $contents = Excel::raw(new StaffImportFormatExport([
            [
                'Rahul',
                'Raj',
                'rahul.raj@example.com',
                '',
                '9876543210',
                'Site Engineer',
                '',
                'Streetlight Project',
                'Field',
                'Operations',
                '',
                '',
                'Patna, Bihar',
            ],
        ]), ExcelFormat::XLSX);

        $path = tempnam(sys_get_temp_dir(), 'staff-import-') . '.xlsx';
        file_put_contents($path, $contents);

        $file = new UploadedFile(
            $path,
            'staff-import.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->actingAs($this->admin)->post(route('import.staff'), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $staff = User::where('email', 'rahul.raj@example.com')->firstOrFail();

        $this->assertSame(UserRole::SITE_ENGINEER->value, (int) $staff->role);
        $this->assertSame(10, (int) $staff->project_id);
        $this->assertDatabaseHas('project_user', [
            'user_id' => $staff->id,
            'project_id' => 10,
            'role' => UserRole::SITE_ENGINEER->value,
        ]);
    }

    public function test_staff_import_creates_user_and_project_assignment(): void
    {
        DB::table('projects')->insert([
            'id' => 10,
            'project_name' => 'Streetlight Project',
            'project_type' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $import = new StaffImport();
        $import->collection(new Collection([
            [
                'first_name' => 'Rahul',
                'last_name' => 'Raj',
                'email' => 'rahul.raj@example.com',
                'contact_number' => '9876543210',
                'role' => 'Site Engineer',
                'project' => 'Streetlight Project',
                'category' => 'Field',
                'department' => 'Operations',
            ],
        ]));

        $summary = $import->getSummary();

        $this->assertSame(1, $summary['created']);
        $this->assertSame(0, $summary['skipped']);

        $staff = User::where('email', 'rahul.raj@example.com')->firstOrFail();
        $this->assertSame(UserRole::SITE_ENGINEER->value, (int) $staff->role);
        $this->assertSame(10, (int) $staff->project_id);

        $this->assertDatabaseHas('project_user', [
            'user_id' => $staff->id,
            'project_id' => 10,
            'role' => UserRole::SITE_ENGINEER->value,
        ]);
    }
}
