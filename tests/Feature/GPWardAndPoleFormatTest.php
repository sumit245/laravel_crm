<?php

namespace Tests\Feature;

use App\Models\Pole;
use App\Models\Project;
use App\Models\Settings\PoleNumberFormat;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Services\Settings\PoleNumberFormatService;
use App\Services\Streetlight\SiteWardService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GPWardAndPoleFormatTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'pole_number_regeneration_batches',
            'streelight_poles',
            'streetlight_task_wards',
            'streetlight_site_wards',
            'streetlight_tasks',
            'streetlights',
            'pole_number_formats',
            'projects',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_name')->nullable();
            $table->integer('project_type')->default(1);
            $table->timestamps();
        });

        Schema::create('streetlights', function (Blueprint $table) {
            $table->id();
            $table->string('task_id')->nullable();
            $table->string('state');
            $table->string('district');
            $table->string('block');
            $table->string('panchayat');
            $table->string('ward')->nullable();
            $table->string('total_poles')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->timestamps();
        });

        Schema::create('streetlight_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('status')->default('Pending');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('allotted_wards')->nullable();
            $table->timestamps();
        });

        Schema::create('streetlight_site_wards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('streetlight_id');
            $table->string('ward_type', 20);
            $table->string('ward_number', 50);
            $table->unsignedTinyInteger('planned_poles')->default(10);
            $table->string('source', 30)->default('site');
            $table->timestamps();
            $table->unique(['streetlight_id', 'ward_type', 'ward_number']);
        });

        Schema::create('streetlight_task_wards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('streetlight_task_id');
            $table->unsignedBigInteger('streetlight_site_ward_id');
            $table->timestamps();
        });

        Schema::create('pole_number_formats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->string('ward_type', 20);
            $table->string('name');
            $table->json('tokens');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('streelight_poles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('streetlight_site_ward_id')->nullable();
            $table->boolean('isSurveyDone')->default(false);
            $table->string('ward_name')->nullable();
            $table->string('ward_type', 20)->nullable();
            $table->string('ward_number', 50)->nullable();
            $table->unsignedInteger('pole_sequence')->nullable();
            $table->unsignedBigInteger('pole_number_format_id')->nullable();
            $table->string('complete_pole_number')->nullable();
            $table->timestamps();
        });
    }

    public function test_normal_and_gp_ward_can_share_same_number(): void
    {
        $project = Project::create(['project_name' => 'Lakhisarai', 'project_type' => 1]);
        $site = Streetlight::create([
            'task_id' => 'LAK001',
            'state' => 'Bihar',
            'district' => 'Lakhisarai',
            'block' => 'Surajgarha',
            'panchayat' => 'Kawadpur',
            'ward' => '3',
            'project_id' => $project->id,
        ]);

        app(SiteWardService::class)->syncSiteWards($site, '3', '3:4', 'test');

        $this->assertDatabaseHas('streetlight_site_wards', [
            'streetlight_id' => $site->id,
            'ward_type' => 'normal',
            'ward_number' => '3',
            'planned_poles' => 10,
        ]);
        $this->assertDatabaseHas('streetlight_site_wards', [
            'streetlight_id' => $site->id,
            'ward_type' => 'gp',
            'ward_number' => '3',
            'planned_poles' => 4,
        ]);
        $this->assertSame('14', (string) $site->fresh()->total_poles);
    }

    public function test_pole_number_format_generates_gp_number_from_saved_data(): void
    {
        $project = Project::create(['project_name' => 'Lakhisarai', 'project_type' => 1]);
        $site = Streetlight::create([
            'task_id' => 'LAK001',
            'state' => 'Bihar',
            'district' => 'Lakhisarai',
            'block' => 'Surajgarha',
            'panchayat' => 'Kawadpur',
            'project_id' => $project->id,
        ]);

        app(SiteWardService::class)->syncSiteWards($site, '3', '3:4', 'test');
        $gpWard = $site->siteWards()->where('ward_type', 'gp')->firstOrFail();
        $task = StreetlightTask::create([
            'project_id' => $project->id,
            'site_id' => $site->id,
            'start_date' => now(),
            'end_date' => now(),
        ]);
        $pole = Pole::create([
            'task_id' => $task->id,
            'streetlight_site_ward_id' => $gpWard->id,
            'ward_type' => 'gp',
            'ward_number' => '3',
            'ward_name' => 'GP Ward 3',
            'pole_sequence' => 2,
            'complete_pole_number' => 'OLD',
        ]);

        $format = PoleNumberFormat::create([
            'project_id' => null,
            'ward_type' => 'gp',
            'name' => 'Test GP',
            'is_active' => true,
            'tokens' => app(PoleNumberFormatService::class)->normalizeTokens([
                'prefix' => ['enabled' => 1, 'value' => 'SUG', 'separator_after' => '/'],
                'district' => ['enabled' => 1, 'length' => 3, 'separator_after' => '/'],
                'panchayat' => ['enabled' => 1, 'length' => 3, 'separator_after' => '/'],
                'ward_label' => ['enabled' => 1, 'value' => 'GP', 'separator_after' => ''],
                'ward_number' => ['enabled' => 1, 'pad' => 2, 'separator_after' => '/'],
                'pole_number' => ['enabled' => 1, 'pad' => 2, 'separator_after' => ''],
            ]),
        ]);

        $this->assertSame(
            'SUG/LAK/KAW/GP03/02',
            app(PoleNumberFormatService::class)->generateForPole($pole, $format)
        );
    }
}
