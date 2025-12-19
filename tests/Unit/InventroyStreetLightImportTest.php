<?php

namespace Tests\Unit;

use App\Imports\InventroyStreetLight;
use App\Models\InventroyStreetLightModel;
use Tests\TestCase;

class InventroyStreetLightImportTest extends TestCase
{
    /** @test */
    public function it_creates_inventory_for_valid_row()
    {
        $import = new InventroyStreetLight(projectId: 1, storeId: 2);

        $row = [
            'item_code' => 'SL01',
            'item' => 'Module',
            'manufacturer' => 'Test Manufacturer',
            'make' => 'Sugs',
            'model' => 'M-100',
            'serial_number' => 'SN-IMP-001',
            'hsn' => '123456',
            'unit' => 'PCS',
            'unit_rate' => 100,
            'quantity' => 1,
            'total_value' => 100,
            'description' => 'Imported item',
            'e-way_bill' => 'EWB-1',
            'received_date' => now()->format('Y-m-d'),
            'sim_number' => null,
        ];

        $model = $import->model($row);
        $model->save();

        $this->assertDatabaseHas('inventory_streetlight', [
            'project_id' => 1,
            'store_id' => 2,
            'item_code' => 'SL01',
            'serial_number' => 'SN-IMP-001',
            'make' => 'Sugs',
            'rate' => 100,
            'quantity' => 1,
            'total_value' => 100,
        ]);
    }

    /** @test */
    public function it_rejects_invalid_item_codes()
    {
        $import = new InventroyStreetLight(projectId: 1, storeId: 2);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Import failed: Invalid item code 'BAD' for streetlight project.");

        $import->model([
            'item_code' => 'BAD',
            'item' => 'Invalid',
            'manufacturer' => 'X',
            'make' => 'Y',
            'model' => 'Z',
            'serial_number' => 'SN-BAD-001',
            'hsn' => '123456',
            'unit' => 'PCS',
            'unit_rate' => 100,
            'quantity' => 1,
            'total_value' => 100,
            'received_date' => now()->format('Y-m-d'),
        ]);
    }

    /** @test */
    public function it_rejects_zero_quantity()
    {
        $import = new InventroyStreetLight(projectId: 1, storeId: 2);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Import failed: Quantity cannot be zero for item code 'SL01'");

        $import->model([
            'item_code' => 'SL01',
            'item' => 'Module',
            'manufacturer' => 'Test',
            'make' => 'Sugs',
            'model' => 'M-100',
            'serial_number' => 'SN-QTY-0',
            'hsn' => '123456',
            'unit' => 'PCS',
            'unit_rate' => 100,
            'quantity' => 0,
            'total_value' => 0,
            'received_date' => now()->format('Y-m-d'),
        ]);
    }

    /** @test */
    public function it_rejects_duplicate_serial_numbers()
    {
        InventroyStreetLightModel::create([
            'project_id' => 1,
            'store_id' => 2,
            'item_code' => 'SL01',
            'item' => 'Module',
            'manufacturer' => 'Existing',
            'make' => 'Sugs',
            'model' => 'M-100',
            'serial_number' => 'SN-DUP-IMP',
            'hsn' => '123456',
            'unit' => 'PCS',
            'rate' => 100,
            'quantity' => 1,
            'total_value' => 100,
            'received_date' => now(),
        ]);

        $import = new InventroyStreetLight(projectId: 1, storeId: 2);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Import failed: Duplicate serial number 'SN-DUP-IMP' found.");

        $import->model([
            'item_code' => 'SL01',
            'item' => 'Module',
            'manufacturer' => 'New',
            'make' => 'Sugs',
            'model' => 'M-101',
            'serial_number' => 'SN-DUP-IMP',
            'hsn' => '123456',
            'unit' => 'PCS',
            'unit_rate' => 100,
            'quantity' => 1,
            'total_value' => 100,
            'received_date' => now()->format('Y-m-d'),
        ]);
    }

    /** @test */
    public function it_rejects_duplicate_sim_numbers_for_luminary_items_only()
    {
        // Existing luminary with SIM
        InventroyStreetLightModel::create([
            'project_id' => 1,
            'store_id' => 2,
            'item_code' => 'SL02',
            'item' => 'Luminary',
            'manufacturer' => 'Existing',
            'make' => 'Sugs',
            'model' => 'L-100',
            'serial_number' => 'SN-LUM-001',
            'sim_number' => 'SIM-IMPORT-1',
            'hsn' => '123456',
            'unit' => 'PCS',
            'rate' => 100,
            'quantity' => 1,
            'total_value' => 100,
            'received_date' => now(),
        ]);

        $import = new InventroyStreetLight(projectId: 1, storeId: 2);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Import failed: Duplicate SIM number 'SIM-IMPORT-1' found for luminary item.");

        $import->model([
            'item_code' => 'SL02',
            'item' => 'Luminary',
            'manufacturer' => 'New',
            'make' => 'Sugs',
            'model' => 'L-101',
            'serial_number' => 'SN-LUM-002',
            'hsn' => '123456',
            'unit' => 'PCS',
            'unit_rate' => 100,
            'quantity' => 1,
            'total_value' => 100,
            'received_date' => now()->format('Y-m-d'),
            'sim_number' => 'SIM-IMPORT-1',
        ]);
    }
}


