<?php

namespace Tests\Unit;

use App\Imports\InventroyStreetLight;
use App\Models\InventroyStreetLightModel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InventroyStreetLightImportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('inventory_streetlight');
        Schema::create('inventory_streetlight', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('store_id');
            $table->string('item_code')->nullable();
            $table->string('item')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('sim_number')->nullable();
            $table->string('hsn')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('rate', 12, 2)->default(0);
            $table->integer('quantity')->default(0);
            $table->decimal('total_value', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('eway_bill')->nullable();
            $table->date('received_date')->nullable();
            $table->timestamps();
        });
    }

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
    public function it_accepts_customer_defined_item_codes()
    {
        $import = new InventroyStreetLight(projectId: 1, storeId: 2);

        $model = $import->model([
            'item_code' => 'CUSTOM-BAT-01',
            'item' => 'Battery',
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

        $this->assertSame('CUSTOM-BAT-01', $model->item_code);
        $this->assertSame('Battery', $model->item);
        $this->assertSame([], $import->getErrors());
    }

    /** @test */
    public function it_rejects_zero_quantity()
    {
        $import = new InventroyStreetLight(projectId: 1, storeId: 2);

        $model = $import->model([
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

        $this->assertNull($model);
        $this->assertSame('zero_quantity', $import->getErrors()[0]['reason']);
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

        $model = $import->model([
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

        $this->assertNull($model);
        $this->assertSame('duplicate_serial_in_db', $import->getErrors()[0]['reason']);
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

        $model = $import->model([
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

        $this->assertNull($model);
        $this->assertSame('duplicate_sim_in_db', $import->getErrors()[0]['reason']);
    }

    /** @test */
    public function it_preserves_external_solar_codes_and_uses_item_name_for_behavior()
    {
        $import = new InventroyStreetLight(projectId: 1, storeId: 2);

        $battery = $import->model([
            'item_code' => 'SOLAR01',
            'item' => 'Battery',
            'manufacturer' => 'Test',
            'make' => 'Sugs',
            'model' => 'BAT-100',
            'serial_number' => 'BAT-ALIAS-001',
            'hsn' => '123456',
            'unit' => 'PCS',
            'unit_rate' => 100,
            'quantity' => 1,
            'total_value' => 100,
            'received_date' => now()->format('Y-m-d'),
        ]);

        $luminary = $import->model([
            'item_code' => 'SOLAR02',
            'item' => 'Luminary',
            'manufacturer' => 'Test',
            'make' => 'Sugs',
            'model' => 'LUM-100',
            'serial_number' => 'LUM-ALIAS-001',
            'sim_number' => 'SIM-ALIAS-001',
            'hsn' => '123456',
            'unit' => 'PCS',
            'unit_rate' => 100,
            'quantity' => 1,
            'total_value' => 100,
            'received_date' => now()->format('Y-m-d'),
        ]);

        $panel = $import->model([
            'item_code' => 'SOLAR03',
            'item' => 'Panel',
            'manufacturer' => 'Test',
            'make' => 'Sugs',
            'model' => 'PNL-100',
            'serial_number' => 'PNL-ALIAS-001',
            'hsn' => '123456',
            'unit' => 'PCS',
            'unit_rate' => 100,
            'quantity' => 1,
            'total_value' => 100,
            'received_date' => now()->format('Y-m-d'),
        ]);

        $this->assertSame('SOLAR01', $battery->item_code);
        $this->assertSame('SOLAR02', $luminary->item_code);
        $this->assertSame('SOLAR03', $panel->item_code);
        $this->assertSame('SIM-ALIAS-001', $luminary->sim_number);
        $this->assertSame(3, $import->getImportedCount());
    }
}
