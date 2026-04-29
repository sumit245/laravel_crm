<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Inventory;
use App\Models\InventoryDispatch;
use App\Models\Project;
use App\Models\Stores;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TransactionIntegrityTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private Project $project;
    private Stores $store;
    private User $vendor;
    private User $storeIncharge;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('inventory_dispatch');
        Schema::dropIfExists('inventory');
        Schema::dropIfExists('stores');
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
            $table->timestamps();
        });

        Schema::create('project_user', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('role')->nullable();
            $table->timestamps();
        });

        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('store_name')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('productName')->nullable();
            $table->string('brand')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('quantity', 10, 2)->nullable()->default(0);
            $table->timestamps();
        });

        Schema::create('inventory_dispatch', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('store_incharge_id')->nullable();
            $table->decimal('quantity', 10, 2)->nullable();
            $table->timestamp('dispatch_date')->nullable();
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
            'name'     => 'Admin',
            'username' => 'admin_user',
            'email'    => 'admin@example.com',
            'password' => Hash::make('password'),
            'role'     => UserRole::ADMIN->value,
            'status'   => 'active',
        ]);

        $this->vendor = User::create([
            'name'     => 'Vendor One',
            'username' => 'vendor_one',
            'email'    => 'vendor@example.com',
            'password' => Hash::make('password'),
            'role'     => UserRole::VENDOR->value,
            'status'   => 'active',
        ]);

        $this->storeIncharge = User::create([
            'name'     => 'Store Incharge',
            'username' => 'store_incharge',
            'email'    => 'storeincharge@example.com',
            'password' => Hash::make('password'),
            'role'     => UserRole::STORE_INCHARGE->value,
            'status'   => 'active',
        ]);

        $this->project = Project::create([
            'project_name'      => 'Test Project',
            'project_type'      => 0,
            'work_order_number' => 'WO-TX-001',
        ]);

        $this->store = Stores::create([
            'project_id' => $this->project->id,
            'store_name' => 'Main Store',
        ]);
    }

    // ─── Inventory dispatch transaction ───────────────────────────────────────

    public function test_dispatch_rolls_back_all_items_when_one_has_insufficient_stock(): void
    {
        $item1 = Inventory::create([
            'project_id' => $this->project->id,
            'store_id'   => $this->store->id,
            'productName'=> 'Panel',
            'brand'      => 'Brand',
            'unit'       => 'pcs',
            'quantity'   => 10,
        ]);

        $item2 = Inventory::create([
            'project_id' => $this->project->id,
            'store_id'   => $this->store->id,
            'productName'=> 'Battery',
            'brand'      => 'Brand',
            'unit'       => 'pcs',
            'quantity'   => 0, // insufficient stock — should cause rollback
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/inventory/dispatch/vendor', [
                'vendor_id'        => $this->vendor->id,
                'project_id'       => $this->project->id,
                'store_id'         => $this->store->id,
                'store_incharge_id'=> $this->storeIncharge->id,
                'items'            => [
                    ['inventory_id' => $item1->id, 'quantity' => 5],
                    ['inventory_id' => $item2->id, 'quantity' => 1], // will fail
                ],
            ]);

        // Should return 400 (stock error)
        $response->assertStatus(400);

        // item1 stock must NOT have been decremented (transaction rolled back)
        $this->assertDatabaseHas('inventory', ['id' => $item1->id, 'quantity' => 10]);

        // No dispatch records should exist
        $this->assertDatabaseMissing('inventory_dispatch', ['vendor_id' => $this->vendor->id]);
    }

    public function test_dispatch_succeeds_when_all_items_have_sufficient_stock(): void
    {
        $item1 = Inventory::create([
            'project_id' => $this->project->id,
            'store_id'   => $this->store->id,
            'productName'=> 'Panel',
            'brand'      => 'Brand',
            'unit'       => 'pcs',
            'quantity'   => 20,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/inventory/dispatch/vendor', [
                'vendor_id'        => $this->vendor->id,
                'project_id'       => $this->project->id,
                'store_id'         => $this->store->id,
                'store_incharge_id'=> $this->storeIncharge->id,
                'items'            => [
                    ['inventory_id' => $item1->id, 'quantity' => 5],
                ],
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('inventory', ['id' => $item1->id, 'quantity' => 15]);
        $this->assertDatabaseHas('inventory_dispatch', [
            'inventory_id' => $item1->id,
            'quantity'     => 5,
        ]);
    }
}
