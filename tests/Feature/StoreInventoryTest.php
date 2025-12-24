<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Stores;
use App\Models\Inventory;

class StoreInventoryTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Show full exceptions during tests
        $this->withoutExceptionHandling();

        // Create minimal schema required for these tests to run in sqlite in-memory
        Schema::dropIfExists('inventory_dispatch');
        Schema::dropIfExists('inventory_streetlight');
        Schema::dropIfExists('inventory');
        Schema::dropIfExists('stores');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->string('role')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->integer('project_type')->default(0);
            $table->string('name')->nullable();
            $table->timestamps();
        });

        // Pivot table for project-user assignments (used by StoreController)
        Schema::create('project_user', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id');
        });

        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('store_name')->nullable();
            $table->string('address')->nullable();
            $table->unsignedBigInteger('store_incharge_id')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item')->nullable();
            $table->string('serial_number')->nullable();
            $table->integer('quantityStock')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamp('received_date')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_dispatch', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->nullable();
            $table->string('serial_number')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->boolean('isDispatched')->default(false);
            $table->boolean('is_consumed')->default(false);
            $table->integer('total_quantity')->default(0);
            $table->integer('total_value')->default(0);
            $table->timestamp('dispatch_date')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->timestamps();
        });
    }

    public function test_show_displays_initial_inventory_rows()
    {
        // Create user and act as admin
        $user = User::factory()->create(['role' => \App\Enums\UserRole::ADMIN->value]);
        $this->actingAs($user);

        // Create project and store
        $project = Project::create(['project_type' => 0, 'name' => 'Test Project']);
        $store = Stores::create([
            'project_id' => $project->id,
            'store_name' => 'Test Store',
            'address' => 'Test Address',
            'store_incharge_id' => $user->id,
        ]);

        // Create a couple of inventory rows
        $items = [];
        for ($i = 1; $i <= 3; $i++) {
            $items[] = Inventory::create([
                'project_id' => $project->id,
                'store_id' => $store->id,
                'item_code' => 'IT' . $i,
                'item' => 'Item ' . $i,
                'serial_number' => 'SERI' . $i,
                'quantityStock' => 1,
                'quantity' => 1,
                'received_date' => now(),
            ]);
        }

        $response = $this->get(route('store.show', $store->id));
        $response->assertStatus(200);

        // Ensure the table contains the serial numbers of the created items
        foreach ($items as $item) {
            $response->assertSee($item->serial_number);
        }
    }

    public function test_inventory_data_returns_expected_json()
    {
        $user = User::factory()->create(['role' => \App\Enums\UserRole::ADMIN->value]);
        $this->actingAs($user);

        $project = Project::create(['project_type' => 0, 'name' => 'Test Project']);
        $store = Stores::create([
            'project_id' => $project->id,
            'store_name' => 'Test Store',
            'address' => 'Test Address',
            'store_incharge_id' => $user->id,
        ]);

        // Create 10 inventory rows
        for ($i = 1; $i <= 10; $i++) {
            Inventory::create([
                'project_id' => $project->id,
                'store_id' => $store->id,
                'item_code' => 'IT' . $i,
                'item' => 'Item ' . $i,
                'serial_number' => 'SERI' . $i,
                'quantityStock' => 1,
                'quantity' => 1,
                'received_date' => now(),
            ]);
        }

        $response = $this->getJson(route('store.inventory.data', $store->id) . '?start=0&length=5&draw=1');
        $response->assertStatus(200);
        $json = $response->json();

        $this->assertArrayHasKey('recordsTotal', $json);
        $this->assertArrayHasKey('recordsFiltered', $json);
        $this->assertArrayHasKey('data', $json);
        $this->assertEquals(10, $json['recordsTotal']);
        $this->assertCount(5, $json['data']);
    }
}
