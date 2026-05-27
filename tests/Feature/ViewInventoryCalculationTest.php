<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\InventoryDispatch;
use App\Models\InventroyStreetLightModel;
use App\Models\Project;
use App\Models\Stores;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ViewInventoryCalculationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

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
            $table->integer('project_type')->default(1);
            $table->string('project_name')->nullable();
            $table->timestamps();
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
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_streetlight', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('hsn')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('rate', 12, 2)->nullable();
            $table->decimal('total_value', 12, 2)->nullable();
            $table->timestamp('received_date')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_dispatch', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('store_incharge_id')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item')->nullable();
            $table->decimal('rate', 12, 2)->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->integer('total_quantity')->default(0);
            $table->decimal('total_value', 12, 2)->default(0);
            $table->string('serial_number')->nullable();
            $table->boolean('isDispatched')->default(false);
            $table->boolean('is_consumed')->default(false);
            $table->timestamp('dispatch_date')->nullable();
            $table->unsignedBigInteger('streetlight_pole_id')->nullable();
            $table->timestamps();
        });
    }

    public function test_view_inventory_calculates_totals_for_each_store_independently(): void
    {
        $admin = User::factory()->create([
            'firstName' => 'Admin',
            'lastName' => 'User',
            'role' => UserRole::ADMIN->value,
        ]);
        $vendor = User::factory()->create([
            'firstName' => 'Vendor',
            'lastName' => 'One',
            'role' => UserRole::VENDOR->value,
        ]);

        $project = Project::create([
            'project_type' => 1,
            'project_name' => 'Streetlight Project',
        ]);

        $northStore = Stores::create([
            'project_id' => $project->id,
            'store_name' => 'North Store',
            'store_incharge_id' => $admin->id,
        ]);
        $southStore = Stores::create([
            'project_id' => $project->id,
            'store_name' => 'South Store',
            'store_incharge_id' => $admin->id,
        ]);

        $this->seedStreetlightInventory($project, $northStore, 'SL03', 'Battery', 3, 100);
        $this->seedStreetlightInventory($project, $northStore, 'SL02', 'Luminary', 2, 250);
        $this->seedStreetlightInventory($project, $northStore, 'SL01', 'Module', 1, 500);
        $this->seedDispatch($project, $northStore, $vendor, 'SL03', 'Battery', 'N-SL03-1', 100);
        $this->seedDispatch($project, $northStore, $vendor, 'SL02', 'Luminary', 'N-SL02-1', 250);

        $this->seedStreetlightInventory($project, $southStore, 'SL03', 'Battery', 1, 90);
        $this->seedStreetlightInventory($project, $southStore, 'SL02', 'Luminary', 1, 200);
        $this->seedStreetlightInventory($project, $southStore, 'SL01', 'Module', 2, 300);
        $this->seedDispatch($project, $southStore, $vendor, 'SL01', 'Module', 'S-SL01-1', 300);

        $northResponse = $this->actingAs($admin)->get(route('inventory.view', [
            'project_id' => $project->id,
            'store_id' => $northStore->id,
        ]));

        $northResponse->assertOk();
        $northResponse->assertViewIs('inventory.view');
        $northResponse->assertViewHasAll([
            'totalBattery' => 3,
            'totalBatteryValue' => '300.00',
            'batteryDispatch' => 1,
            'availableBattery' => 2,
            'dispatchAmountBattery' => '100.00',
            'totalLuminary' => 2,
            'totalLuminaryValue' => '500.00',
            'luminaryDispatch' => 1,
            'availableLuminary' => 1,
            'dispatchAmountLuminary' => '250.00',
            'totalModule' => 1,
            'totalModuleValue' => '500.00',
            'moduleDispatch' => 0,
            'availableModule' => 1,
            'dispatchAmountModule' => '0.00',
            'totalStructure' => 3,
            'totalStructureValue' => 1200,
            'structureDispatch' => 1,
            'availableStructure' => 2,
        ]);

        $southResponse = $this->actingAs($admin)->get(route('inventory.view', [
            'project_id' => $project->id,
            'store_id' => $southStore->id,
        ]));

        $southResponse->assertOk();
        $southResponse->assertViewIs('inventory.view');
        $southResponse->assertViewHasAll([
            'totalBattery' => 1,
            'totalBatteryValue' => '90.00',
            'batteryDispatch' => 0,
            'availableBattery' => 1,
            'dispatchAmountBattery' => '0.00',
            'totalLuminary' => 1,
            'totalLuminaryValue' => '200.00',
            'luminaryDispatch' => 0,
            'availableLuminary' => 1,
            'dispatchAmountLuminary' => '0.00',
            'totalModule' => 2,
            'totalModuleValue' => '600.00',
            'moduleDispatch' => 1,
            'availableModule' => 1,
            'dispatchAmountModule' => '300.00',
            'totalStructure' => 1,
            'totalStructureValue' => 400,
            'structureDispatch' => 0,
            'availableStructure' => 1,
        ]);
    }

    private function seedStreetlightInventory(
        Project $project,
        Stores $store,
        string $itemCode,
        string $item,
        int $count,
        int $rate
    ): void {
        for ($i = 1; $i <= $count; $i++) {
            InventroyStreetLightModel::create([
                'project_id' => $project->id,
                'store_id' => $store->id,
                'item_code' => $itemCode,
                'item' => $item,
                'manufacturer' => 'Test Manufacturer',
                'make' => 'Test Make',
                'model' => 'Test Model',
                'serial_number' => strtoupper(substr($store->store_name, 0, 1)).'-'.$itemCode.'-'.$i,
                'hsn' => 'HSN-'.$itemCode,
                'quantity' => 1,
                'rate' => $rate,
                'total_value' => $rate,
                'received_date' => now(),
            ]);
        }
    }

    private function seedDispatch(
        Project $project,
        Stores $store,
        User $vendor,
        string $itemCode,
        string $item,
        string $serialNumber,
        int $totalValue
    ): void {
        InventoryDispatch::create([
            'vendor_id' => $vendor->id,
            'project_id' => $project->id,
            'store_id' => $store->id,
            'store_incharge_id' => $store->store_incharge_id,
            'item_code' => $itemCode,
            'item' => $item,
            'rate' => $totalValue,
            'make' => 'Test Make',
            'model' => 'Test Model',
            'total_quantity' => 1,
            'total_value' => $totalValue,
            'serial_number' => $serialNumber,
            'isDispatched' => true,
            'is_consumed' => false,
            'dispatch_date' => now(),
        ]);
    }
}
