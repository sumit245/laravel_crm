<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\InventroyStreetLightModel;
use App\Models\Project;
use App\Models\Stores;
use App\Models\User;
use Tests\TestCase;

class InventoryAddStreetlightTest extends TestCase
{
    protected User $admin;
    protected Project $project;
    protected Stores $store;

    protected function setUp(): void
    {
        parent::setUp();

        // Use sqlite in-memory as configured in phpunit.xml
        // This ensures no production data is ever touched.

        $this->admin = User::factory()->create([
            'role' => UserRole::ADMIN->value,
        ]);

        $this->project = Project::factory()->create([
            'project_type' => 1, // Streetlight
        ]);

        $this->store = Stores::factory()->create([
            'project_id' => $this->project->id,
            'store_incharge_id' => $this->admin->id,
        ]);
    }

    /** @test */
    public function single_item_entry_requires_core_fields()
    {
        $response = $this->actingAs($this->admin)->post(route('inventory.store'), [
            'project_type' => 1,
            'project_id' => $this->project->id,
            'store_id' => $this->store->id,
            // Missing item, manufacturer, model, serialnumber
        ]);

        $response->assertSessionHasErrors([
            'code',
            'dropdown',
            'manufacturer',
            'model',
            'serialnumber',
        ]);
    }

    /** @test */
    public function single_item_entry_applies_default_values_for_optional_fields()
    {
        $response = $this->actingAs($this->admin)->post(route('inventory.store'), [
            'project_type' => 1,
            'project_id' => $this->project->id,
            'store_id' => $this->store->id,
            'code' => 'SL01',
            'dropdown' => 'Module',
            'manufacturer' => 'Test Manufacturer',
            'model' => 'Test Model',
            'serialnumber' => 'SN-AUTO-001',
            // Optional fields omitted intentionally
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inventory_streetlight', [
            'project_id' => $this->project->id,
            'store_id' => $this->store->id,
            'item_code' => 'SL01',
            'item' => 'Module',
            'serial_number' => 'SN-AUTO-001',
            'make' => 'Sugs',
            'rate' => 100,
            'hsn' => '123456',
            'unit' => 'PCS',
        ]);

        /** @var InventroyStreetLightModel $item */
        $item = InventroyStreetLightModel::where('serial_number', 'SN-AUTO-001')->firstOrFail();
        $this->assertEquals('', $item->description);
        $this->assertNotNull($item->received_date);
    }

    /** @test */
    public function serial_number_must_be_unique_for_streetlight_inventory()
    {
        InventroyStreetLightModel::create([
            'project_id' => $this->project->id,
            'store_id' => $this->store->id,
            'item_code' => 'SL01',
            'item' => 'Module',
            'manufacturer' => 'Existing',
            'model' => 'Model X',
            'serial_number' => 'SN-DUP-001',
            'make' => 'Sugs',
            'rate' => 100,
            'quantity' => 1,
            'hsn' => '123456',
            'unit' => 'PCS',
            'total_value' => 100,
            'received_date' => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('inventory.store'), [
            'project_type' => 1,
            'project_id' => $this->project->id,
            'store_id' => $this->store->id,
            'code' => 'SL01',
            'dropdown' => 'Module',
            'manufacturer' => 'New',
            'model' => 'Model Y',
            'serialnumber' => 'SN-DUP-001',
        ]);

        $response->assertSessionHasErrors(['serialnumber']);
    }

    /** @test */
    public function sim_number_is_required_and_unique_only_for_luminary_items()
    {
        // Existing luminary with SIM number
        InventroyStreetLightModel::create([
            'project_id' => $this->project->id,
            'store_id' => $this->store->id,
            'item_code' => 'SL02',
            'item' => 'Luminary',
            'manufacturer' => 'Existing',
            'model' => 'L-100',
            'serial_number' => 'SN-LUM-001',
            'sim_number' => 'SIM-EXISTING',
            'make' => 'Sugs',
            'rate' => 100,
            'quantity' => 1,
            'hsn' => '123456',
            'unit' => 'PCS',
            'total_value' => 100,
            'received_date' => now(),
        ]);

        // Missing SIM number for SL02
        $missingSim = $this->actingAs($this->admin)->post(route('inventory.store'), [
            'project_type' => 1,
            'project_id' => $this->project->id,
            'store_id' => $this->store->id,
            'code' => 'SL02',
            'dropdown' => 'Luminary',
            'manufacturer' => 'Test',
            'model' => 'L-101',
            'serialnumber' => 'SN-LUM-002',
        ]);

        $missingSim->assertSessionHasErrors(['sim_number']);

        // Duplicate SIM number for SL02
        $duplicateSim = $this->actingAs($this->admin)->post(route('inventory.store'), [
            'project_type' => 1,
            'project_id' => $this->project->id,
            'store_id' => $this->store->id,
            'code' => 'SL02',
            'dropdown' => 'Luminary',
            'manufacturer' => 'Test',
            'model' => 'L-102',
            'serialnumber' => 'SN-LUM-003',
            'sim_number' => 'SIM-EXISTING',
        ]);

        $duplicateSim->assertSessionHasErrors(['sim_number']);

        // Non-luminary (SL01) ignores SIM number and allows null
        $nonLuminary = $this->actingAs($this->admin)->post(route('inventory.store'), [
            'project_type' => 1,
            'project_id' => $this->project->id,
            'store_id' => $this->store->id,
            'code' => 'SL01',
            'dropdown' => 'Module',
            'manufacturer' => 'Test',
            'model' => 'M-200',
            'serialnumber' => 'SN-MOD-001',
        ]);

        $nonLuminary->assertRedirect();
        $this->assertDatabaseHas('inventory_streetlight', [
            'serial_number' => 'SN-MOD-001',
            'item_code' => 'SL01',
        ]);
    }
}


