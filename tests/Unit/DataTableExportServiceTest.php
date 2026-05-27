<?php

namespace Tests\Unit;

use App\Enums\DataTableExportScope;
use App\Http\Requests\DataTableExportRequest;
use App\Models\Pole;
use App\Services\Export\DataTableExportService;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DataTableExportServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('streelight_poles');
        Schema::create('streelight_poles', function ($table) {
            $table->id();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->string('complete_pole_number')->nullable();
            $table->boolean('isInstallationDone')->default(0);
            $table->boolean('isSurveyDone')->default(0);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('streelight_poles');
        parent::tearDown();
    }

    public function test_filtered_all_applies_no_pagination_by_default(): void
    {
        Pole::query()->insert([
            ['complete_pole_number' => 'P-1', 'isInstallationDone' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['complete_pole_number' => 'P-2', 'isInstallationDone' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['complete_pole_number' => 'P-3', 'isInstallationDone' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $request = DataTableExportRequest::create('/', 'GET', [
            'export_scope' => DataTableExportScope::FilteredAll->value,
        ]);

        $service = new DataTableExportService();
        $query = $service->applyExportScope(Pole::query()->where('isInstallationDone', 1), $request);

        $this->assertSame(3, $query->count());
    }

    public function test_page_range_limits_rows(): void
    {
        foreach (range(1, 5) as $i) {
            Pole::query()->insert([
                'complete_pole_number' => 'P-' . $i,
                'isInstallationDone' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $request = DataTableExportRequest::create('/', 'GET', [
            'export_scope' => DataTableExportScope::PageRange->value,
            'page_from' => 2,
            'page_to' => 2,
            'length' => 2,
        ]);

        $service = new DataTableExportService();
        $query = $service->applyExportScope(Pole::query()->orderBy('id'), $request);

        $this->assertCount(2, $query->get());
    }
}
