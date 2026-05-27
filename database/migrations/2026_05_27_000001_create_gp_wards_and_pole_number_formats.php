<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streetlight_site_wards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('streetlight_id')->constrained('streetlights')->cascadeOnDelete();
            $table->string('ward_type', 20)->default('normal');
            $table->string('ward_number', 50);
            $table->unsignedTinyInteger('planned_poles')->default(10);
            $table->string('source', 30)->default('site');
            $table->timestamps();

            $table->unique(['streetlight_id', 'ward_type', 'ward_number'], 'site_ward_type_number_unique');
            $table->index(['streetlight_id', 'ward_type']);
        });

        Schema::create('streetlight_task_wards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('streetlight_task_id')->constrained('streetlight_tasks')->cascadeOnDelete();
            $table->foreignId('streetlight_site_ward_id')->constrained('streetlight_site_wards')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['streetlight_task_id', 'streetlight_site_ward_id'], 'task_site_ward_unique');
        });

        Schema::create('pole_number_formats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('ward_type', 20)->default('normal');
            $table->string('name')->default('Default');
            $table->json('tokens');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'ward_type', 'is_active'], 'pole_format_scope_index');
        });

        Schema::create('pole_number_regeneration_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pole_number_format_id')->constrained('pole_number_formats')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('ward_type', 20);
            $table->string('status', 30)->default('pending');
            $table->unsignedInteger('affected_count')->default(0);
            $table->unsignedInteger('processed_count')->default(0);
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::table('streelight_poles', function (Blueprint $table) {
            $table->foreignId('streetlight_site_ward_id')->nullable()->after('vendor_id')->constrained('streetlight_site_wards')->nullOnDelete();
            $table->string('ward_type', 20)->nullable()->after('ward_name');
            $table->string('ward_number', 50)->nullable()->after('ward_type');
            $table->unsignedInteger('pole_sequence')->nullable()->after('ward_number');
            $table->foreignId('pole_number_format_id')->nullable()->after('pole_sequence')->constrained('pole_number_formats')->nullOnDelete();
            $table->index(['streetlight_site_ward_id', 'pole_sequence'], 'pole_site_ward_sequence_index');
            $table->index(['ward_type', 'ward_number'], 'pole_ward_type_number_index');
        });

        $this->seedDefaultFormats();
        $this->backfillSiteWards();
        $this->backfillTaskWards();
    }

    public function down(): void
    {
        Schema::table('streelight_poles', function (Blueprint $table) {
            $table->dropForeign(['streetlight_site_ward_id']);
            $table->dropForeign(['pole_number_format_id']);
            $table->dropIndex('pole_site_ward_sequence_index');
            $table->dropIndex('pole_ward_type_number_index');
            $table->dropColumn([
                'streetlight_site_ward_id',
                'ward_type',
                'ward_number',
                'pole_sequence',
                'pole_number_format_id',
            ]);
        });

        Schema::dropIfExists('pole_number_regeneration_batches');
        Schema::dropIfExists('pole_number_formats');
        Schema::dropIfExists('streetlight_task_wards');
        Schema::dropIfExists('streetlight_site_wards');
    }

    private function seedDefaultFormats(): void
    {
        $now = now();
        $baseTokens = [
            ['type' => 'prefix', 'enabled' => true, 'value' => 'SUG', 'separator_after' => '/'],
            ['type' => 'project', 'enabled' => false, 'length' => 3, 'separator_after' => '/'],
            ['type' => 'state', 'enabled' => false, 'length' => 3, 'separator_after' => '/'],
            ['type' => 'district', 'enabled' => true, 'length' => 3, 'separator_after' => '/'],
            ['type' => 'block', 'enabled' => true, 'length' => 3, 'separator_after' => '/'],
            ['type' => 'panchayat', 'enabled' => true, 'length' => 3, 'separator_after' => '/'],
            ['type' => 'ward_label', 'enabled' => true, 'value' => 'W', 'separator_after' => ''],
            ['type' => 'ward_number', 'enabled' => true, 'pad' => 2, 'separator_after' => '/'],
            ['type' => 'pole_number', 'enabled' => true, 'pad' => 2, 'separator_after' => ''],
        ];

        DB::table('pole_number_formats')->insert([
            [
                'project_id' => null,
                'ward_type' => 'normal',
                'name' => 'Global Normal Ward Format',
                'tokens' => json_encode($baseTokens),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'project_id' => null,
                'ward_type' => 'gp',
                'name' => 'Global GP Ward Format',
                'tokens' => json_encode(collect($baseTokens)->map(function ($token) {
                    if ($token['type'] === 'ward_label') {
                        $token['value'] = 'GP';
                    }

                    return $token;
                })->all()),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    private function backfillSiteWards(): void
    {
        DB::table('streetlights')
            ->select(['id', 'ward', 'ward_type'])
            ->orderBy('id')
            ->chunkById(200, function ($sites) {
                $rows = [];
                $now = now();

                foreach ($sites as $site) {
                    foreach ($this->parseLegacyWards($site->ward) as $ward) {
                        $wardType = strtoupper((string) $site->ward_type) === 'GP' ? 'gp' : 'normal';

                        $rows[] = [
                            'streetlight_id' => $site->id,
                            'ward_type' => $wardType,
                            'ward_number' => $ward,
                            'planned_poles' => 10,
                            'source' => 'migration',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if ($rows) {
                    DB::table('streetlight_site_wards')->upsert(
                        $rows,
                        ['streetlight_id', 'ward_type', 'ward_number'],
                        ['planned_poles', 'source', 'updated_at']
                    );
                }
            });
    }

    private function backfillTaskWards(): void
    {
        DB::table('streetlight_tasks')
            ->select(['id', 'site_id', 'allotted_wards'])
            ->whereNotNull('allotted_wards')
            ->orderBy('id')
            ->chunkById(200, function ($tasks) {
                $rows = [];
                $now = now();

                foreach ($tasks as $task) {
                    foreach ($this->parseLegacyWards($task->allotted_wards) as $ward) {
                        $siteWard = DB::table('streetlight_site_wards')
                            ->where('streetlight_id', $task->site_id)
                            ->where('ward_type', 'normal')
                            ->where('ward_number', $ward)
                            ->first();

                        if ($siteWard) {
                            $rows[] = [
                                'streetlight_task_id' => $task->id,
                                'streetlight_site_ward_id' => $siteWard->id,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }
                }

                if ($rows) {
                    DB::table('streetlight_task_wards')->upsert(
                        $rows,
                        ['streetlight_task_id', 'streetlight_site_ward_id'],
                        ['updated_at']
                    );
                }
            });
    }

    private function parseLegacyWards(?string $value): array
    {
        return collect(explode(',', (string) $value))
            ->map(fn ($ward) => trim($ward))
            ->filter(fn ($ward) => $ward !== '' && preg_match('/^\d+$/', $ward))
            ->unique()
            ->values()
            ->all();
    }
};
