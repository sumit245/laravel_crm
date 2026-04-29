<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Inventory;
use App\Models\Project;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MassAssignmentTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('inventory');
        Schema::dropIfExists('project_user');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('username')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            $table->integer('role')->default(1);
            $table->string('status')->nullable()->default('active');
            $table->boolean('disableLogin')->default(false);
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->integer('project_type')->default(0);
            $table->string('project_name')->nullable();
            $table->string('work_order_number')->nullable()->unique();
            $table->string('agreement_number')->nullable();
            $table->date('agreement_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('rate')->nullable();
            $table->timestamps();
        });

        Schema::create('project_user', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });

        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('site_name')->nullable();
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->string('productName')->nullable();
            $table->string('brand')->nullable();
            $table->text('description')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('initialQuantity', 10, 2)->nullable();
            $table->decimal('quantityStock', 10, 2)->nullable();
            $table->decimal('rate', 10, 2)->nullable();
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
            'name'        => 'Admin',
            'firstName'   => 'Admin',
            'lastName'    => 'User',
            'username'    => 'admin_user',
            'email'       => 'admin@example.com',
            'password'    => Hash::make('password'),
            'role'        => UserRole::ADMIN->value,
            'status'      => 'active',
        ]);

        $this->project = Project::create([
            'project_name'      => 'Test Project',
            'project_type'      => 0,
            'work_order_number' => 'WO-001',
            'start_date'        => now()->toDateString(),
        ]);
    }

    // ─── SiteController::update ───────────────────────────────────────────────

    public function test_site_update_only_updates_whitelisted_fields(): void
    {
        $site = Site::create([
            'project_id' => $this->project->id,
            'site_name'  => 'Original Name',
            'state'      => 'State',
            'district'   => 'District',
            'location'   => 'Location',
        ]);

        $originalId = $site->id;

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/site/{$site->id}", [
                'id'        => 99999,          // should be ignored
                'site_name' => 'Updated Name', // should be accepted
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('sites', ['id' => $originalId, 'site_name' => 'Updated Name']);
        $this->assertDatabaseMissing('sites', ['id' => 99999]);
    }

    public function test_site_update_accepts_valid_fields(): void
    {
        $site = Site::create([
            'project_id' => $this->project->id,
            'site_name'  => 'Old Name',
            'state'      => 'Old State',
            'district'   => 'Old District',
            'location'   => 'Old Location',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/site/{$site->id}", [
                'site_name' => 'New Name',
                'state'     => 'New State',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('sites', ['id' => $site->id, 'site_name' => 'New Name', 'state' => 'New State']);
    }

    // ─── InventoryController::update ─────────────────────────────────────────

    public function test_inventory_update_rejects_non_numeric_quantity(): void
    {
        $inv = Inventory::create([
            'productName'  => 'Panel',
            'brand'        => 'BrandX',
            'unit'         => 'pcs',
            'quantityStock'=> 100,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/inventories/{$inv->id}", [
                'quantityStock' => 'abc', // should fail validation
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['quantityStock']);
    }

    public function test_inventory_update_rejects_negative_quantity(): void
    {
        $inv = Inventory::create([
            'productName'  => 'Panel',
            'brand'        => 'BrandX',
            'unit'         => 'pcs',
            'quantityStock'=> 100,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/inventories/{$inv->id}", [
                'quantityStock' => -5,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['quantityStock']);
    }

    public function test_inventory_update_accepts_valid_fields(): void
    {
        $inv = Inventory::create([
            'productName'  => 'Panel',
            'brand'        => 'BrandX',
            'unit'         => 'pcs',
            'quantityStock'=> 100,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/inventories/{$inv->id}", [
                'productName' => 'Updated Panel',
                'brand'       => 'BrandY',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('inventory', ['id' => $inv->id, 'productName' => 'Updated Panel', 'brand' => 'BrandY']);
    }
}
