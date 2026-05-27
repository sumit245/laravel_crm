<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('legal_name')->nullable();
            $table->string('gst_number')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->timestamps();
        });

        Schema::create('vendor_earning_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->decimal('pole_rate', 12, 2)->default(500);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('module_permissions', function (Blueprint $table) {
            $table->id();
            $table->integer('role');
            $table->string('module_key');
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_export')->default(false);
            $table->timestamps();
            $table->unique(['role', 'module_key']);
        });

        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->unique();
            $table->boolean('enabled')->default(true);
            $table->json('recipient_roles')->nullable();
            $table->boolean('database_enabled')->default(true);
            $table->boolean('mail_enabled')->default(false);
            $table->boolean('whatsapp_enabled')->default(false);
            $table->timestamps();
        });

        Schema::create('import_export_settings', function (Blueprint $table) {
            $table->id();
            $table->string('module_key')->unique();
            $table->json('allowed_file_types')->nullable();
            $table->unsignedInteger('max_file_size_kb')->default(5120);
            $table->string('sample_format_path')->nullable();
            $table->timestamps();
        });

        Schema::create('rms_settings', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint')->nullable();
            $table->string('auth_mode')->default('none');
            $table->boolean('enabled')->default(false);
            $table->unsignedInteger('timeout_seconds')->default(30);
            $table->unsignedInteger('retry_count')->default(3);
            $table->timestamps();
        });

        Schema::create('dashboard_settings', function (Blueprint $table) {
            $table->id();
            $table->string('default_date_filter')->default('this_month');
            $table->string('default_project_behavior')->default('first_assigned');
            $table->json('visible_widgets')->nullable();
            $table->timestamps();
        });

        Schema::create('settings_change_logs', function (Blueprint $table) {
            $table->id();
            $table->string('setting_group');
            $table->string('setting_key')->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();
        });

        DB::table('organization_settings')->insert([
            'company_name' => 'Sugs Lloyd Ltd',
            'legal_name' => 'Sugs Lloyd Ltd',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('vendor_earning_settings')->insert([
            'project_id' => null,
            'pole_rate' => 500,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('rms_settings')->insert([
            'enabled' => false,
            'auth_mode' => 'none',
            'timeout_seconds' => 30,
            'retry_count' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('dashboard_settings')->insert([
            'default_date_filter' => 'this_month',
            'default_project_behavior' => 'first_assigned',
            'visible_widgets' => json_encode(['performance', 'meetings', 'tada', 'top_performers']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $modules = ['dashboard', 'projects', 'sites', 'tasks', 'inventory', 'staff', 'vendors', 'billing', 'meetings', 'performance', 'hrm', 'rms', 'backup', 'settings'];
        $permissionRows = [];
        foreach (UserRole::cases() as $role) {
            foreach ($modules as $module) {
                $isAdmin = $role === UserRole::ADMIN;
                $permissionRows[] = [
                    'role' => $role->value,
                    'module_key' => $module,
                    'can_view' => $isAdmin,
                    'can_create' => $isAdmin,
                    'can_update' => $isAdmin,
                    'can_delete' => $isAdmin,
                    'can_export' => $isAdmin,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('module_permissions')->insert($permissionRows);

        DB::table('notification_settings')->insert([
            [
                'event_key' => 'activity_log_created',
                'enabled' => true,
                'recipient_roles' => json_encode([UserRole::ADMIN->value, UserRole::PROJECT_MANAGER->value, UserRole::COORDINATOR->value]),
                'database_enabled' => true,
                'mail_enabled' => false,
                'whatsapp_enabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        foreach (['staff', 'vendors', 'sites', 'tasks', 'inventory', 'candidates'] as $module) {
            DB::table('import_export_settings')->insert([
                'module_key' => $module,
                'allowed_file_types' => json_encode(['xlsx', 'xls']),
                'max_file_size_kb' => 5120,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings_change_logs');
        Schema::dropIfExists('dashboard_settings');
        Schema::dropIfExists('rms_settings');
        Schema::dropIfExists('import_export_settings');
        Schema::dropIfExists('notification_settings');
        Schema::dropIfExists('module_permissions');
        Schema::dropIfExists('vendor_earning_settings');
        Schema::dropIfExists('organization_settings');
    }
};
