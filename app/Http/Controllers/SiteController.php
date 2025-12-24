<?php

namespace App\Http\Controllers;

use App\Imports\SiteImport;
use App\Imports\SitePoleImport;
use App\Imports\StreetlightImport;
use App\Helpers\ExcelHelper;
use App\Models\City;
use App\Models\Pole;
use App\Models\Project;
use App\Models\Site;
use App\Models\State;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SiteController extends Controller
{
    public function import(Request $request, $projectId)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        $project = Project::find($request->project_id);
        if (!$project) {
            return back()->with('error', 'Project not found.');
        }

        try {
            if ($project->project_type == 1) {
                $import = new StreetlightImport($projectId);
                Excel::import($import, $request->file('file'));

                $errors = $import->getErrors();
                $importedCount = $import->getImportedCount();
                $updatedCount = $import->getUpdatedCount();
                $skippedCount = $import->getSkippedCount();
                $errorFileUrl = null;

                if (!empty($errors)) {
                    // Build a downloadable errors.txt file
                    $lines = [];
                    $lines[] = 'Streetlight Site Import Errors - ' . now()->toDateTimeString();
                    $lines[] = 'Project ID: ' . $projectId;
                    $lines[] = str_repeat('=', 80);

                    foreach ($errors as $err) {
                        $lines[] = "Row: " . ($err['row'] ?? 'Unknown');
                        $lines[] = "  District: " . ($err['district'] ?? '');
                        $lines[] = "  Block: " . ($err['block'] ?? '');
                        $lines[] = "  Panchayat: " . ($err['panchayat'] ?? '');
                        if (isset($err['ward'])) {
                            $lines[] = "  Ward: " . $err['ward'];
                        }
                        $lines[] = "  Reason: " . ($err['reason'] ?? 'Unknown error');
                        $lines[] = str_repeat('-', 40);
                    }

                    $content = implode(PHP_EOL, $lines) . PHP_EOL;

                    // Use the public disk so URL generation is reliable
                    $disk = \Illuminate\Support\Facades\Storage::disk('public');
                    if (!$disk->exists('import_errors')) {
                        $disk->makeDirectory('import_errors');
                    }

                    $fileName = 'sites_errors_project_' . $projectId . '_' . time() . '.txt';
                    $relativePath = 'import_errors/' . $fileName;
                    $disk->put($relativePath, $content);

                    // Public URL (requires `php artisan storage:link` once)
                    $errorFileUrl = $disk->url($relativePath);
                }

                $redirect = redirect()->route('projects.show', $projectId)
                    ->with('import_errors_url', $errorFileUrl)
                    ->with('import_errors_count', count($errors))
                    ->with('import_updated_count', $updatedCount);

                if ($importedCount > 0 || $updatedCount > 0) {
                    $message = "Streetlight data imported successfully!";
                    if ($importedCount > 0) {
                        $message .= " Imported: {$importedCount} site(s)";
                    }
                    if ($updatedCount > 0) {
                        $message .= ", Updated: {$updatedCount} site(s)";
                    }
                    if (!empty($errors)) {
                        $message .= ", Skipped rows: " . count($errors);
                    }
                    $redirect->with('success', $message);
                } else {
                    // No new rows imported - treat as warning/error
                    $message = 'No new sites imported. Skipped rows: ' . count($errors);
                    $redirect->withErrors(['error' => $message]);
                }

                return $redirect;
            } else {
                Excel::import(new SiteImport($projectId), $request->file('file'));
                return back()->with('success', 'Sites imported successfully!');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sites = Site::with(['stateRelation', 'districtRelation'])->get();
        return view('sites.index', compact('sites'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, $projectId = null)
    {
        $project = null;
        $projectType = null;

        $projectId = $projectId ?? $request->query('project_id');

        if ($projectId) {
            $project = Project::find($projectId);
            if ($project) {
                $projectType = $project->project_type;
            }
        }

        $states = State::all();
        $projects = Project::all();
        $vendors = User::where('role', \App\Enums\UserRole::VENDOR->value)->get();
        $staffs = User::whereIn('role', [
            \App\Enums\UserRole::SITE_ENGINEER->value,
            \App\Enums\UserRole::PROJECT_MANAGER->value
        ])->get();

        return view('sites.create', compact('states', 'projects', 'vendors', 'staffs', 'project', 'projectType'));
    }

    /**
     * Generate task_id for streetlight sites based on district prefix
     */
    private function generateTaskId($district)
    {
        $districtPrefix = strtoupper(substr($district, 0, 3));

        $lastTask = Streetlight::where('task_id', 'LIKE', "{$districtPrefix}%")
            ->orderBy('task_id', 'desc')
            ->first();

        if ($lastTask) {
            preg_match('/(\d+)$/', $lastTask->task_id, $matches);
            $counter = isset($matches[1]) ? (int) $matches[1] + 1 : 1;
        } else {
            $counter = 1;
        }

        return sprintf('%s%03d', $districtPrefix, $counter);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $projectId = $request->input('project_id');
            $project = Project::find($projectId);

            if (!$project) {
                return redirect()->back()
                    ->withErrors(['error' => 'Project not found.'])
                    ->withInput();
            }

            if ($project->project_type == 1) {
                $validatedData = $request->validate([
                    'project_id' => 'required|integer',
                    'state' => 'required|string|max:255',
                    'district' => 'required|string|max:255',
                    'block' => 'required|string|max:255',
                    'panchayat' => 'required|string|max:255',
                    'ward' => 'nullable|string|max:255',
                    'total_poles' => 'nullable|integer|min:0',
                    'mukhiya_contact' => 'nullable|string|max:255',
                ]);

                // Check for existing site with same District->Block->Panchayat
                $existingSite = Streetlight::where('project_id', $projectId)
                    ->where('district', $validatedData['district'])
                    ->where('block', $validatedData['block'])
                    ->where('panchayat', $validatedData['panchayat'])
                    ->first();

                if ($existingSite) {
                    // Parse wards
                    $existingWards = !empty($existingSite->ward) 
                        ? array_map('intval', explode(',', $existingSite->ward))
                        : [];
                    $newWards = !empty($validatedData['ward']) 
                        ? array_map('intval', explode(',', $validatedData['ward']))
                        : [];

                    // Check if wards are the same
                    sort($existingWards);
                    sort($newWards);
                    $sameWards = $existingWards === $newWards;

                    if ($sameWards) {
                        // Same District->Block->Panchayat->Wards -> Reject
                        return redirect()->back()
                            ->withErrors(['error' => 'Site already exists'])
                            ->withInput();
                    } else {
                        // Different wards -> Update existing site by merging wards
                        $mergedWards = array_unique(array_merge($existingWards, $newWards));
                        sort($mergedWards);
                        $mergedWardsString = implode(',', $mergedWards);

                        // Calculate new total_poles (assuming 10 poles per ward)
                        $newTotalPoles = count($mergedWards) * 10;

                        $existingSite->update([
                            'ward' => $mergedWardsString,
                            'total_poles' => $newTotalPoles,
                            'mukhiya_contact' => !empty($validatedData['mukhiya_contact']) 
                                ? $validatedData['mukhiya_contact'] 
                                : $existingSite->mukhiya_contact,
                        ]);

                        return redirect()->route('projects.show', $projectId)
                            ->with('success', 'Streetlight site updated successfully with merged wards.');
                    }
                }

                // No duplicate found -> Create new site
                $validatedData['task_id'] = $this->generateTaskId($validatedData['district']);
                
                // Calculate total_poles if not provided (10 per ward)
                if (empty($validatedData['total_poles']) && !empty($validatedData['ward'])) {
                    $wardCount = count(array_map('intval', explode(',', $validatedData['ward'])));
                    $validatedData['total_poles'] = $wardCount * 10;
                }
                
                $streetlight = Streetlight::create($validatedData);

                return redirect()->route('projects.show', $projectId)
                    ->with('success', 'Streetlight site created successfully.');
            } else {
                $validatedData = $request->validate([
                    'state' => 'required|integer',
                    'district' => 'required|integer',
                    'location' => 'required|string|max:255',
                    'project_id' => 'required|integer',
                    'site_name' => 'required|string|max:255',
                    'ic_vendor_name' => 'required|integer',
                    'site_engineer' => 'required|integer',
                    'contact_no' => 'required|string',
                    'meter_number' => 'required|string|max:50',
                    'net_meter_sr_no' => 'required|string|max:50',
                    'solar_meter_sr_no' => 'required|string|max:50',
                    'project_capacity' => 'required|numeric',
                    'ca_number' => 'required|string|max:50',
                    'sanction_load' => 'required|numeric',
                    'load_enhancement_status' => 'required|string|max:255',
                    'site_survey_status' => 'required|string|max:255',
                    'material_inspection_date' => 'required|date',
                    'spp_installation_date' => 'required|date',
                    'commissioning_date' => 'required|date',
                    'remarks' => 'nullable|string|max:1000',
                ]);

                $site = Site::create($validatedData);

                return redirect()->route('sites.show', $site->id)
                    ->with('success', 'Site created successfully.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'An error occurred while creating the site. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $projectType = $request->query('project_type');

        if ($projectType == 1) {
            $site = Streetlight::with('streetlightTasks')->findOrFail($id);

            $streetlightTask = StreetlightTask::with(['engineer', 'vendor', 'manager'])
                ->where('site_id', $site->id)
                ->first();

            $poles = Pole::where('task_id', $streetlightTask->id ?? null)
                ->get();

            $engineerName = optional($streetlightTask?->engineer)->firstName . " " . optional($streetlightTask?->engineer)->lastName ?? 'N/A';
            $vendorName = optional($streetlightTask?->vendor)->name ?? 'N/A';
            $managerName = optional($streetlightTask?->manager)->firstName . " " . optional($streetlightTask?->manager)->lastName ?? 'N/A';
            $stateName = $site->state;
            $districtName = $site->district;

            // Prepare ward options for filter
            $wardOptions = [];
            if ($site->ward) {
                $wards = collect(explode(",", $site->ward))
                    ->map(fn($w) => "Ward " . trim($w))
                    ->toArray();
                $wardOptions = array_combine($wards, $wards);
            }

            return view('sites.show', compact(
                'site',
                'streetlightTask',
                'poles',
                'engineerName',
                'vendorName',
                'managerName',
                'stateName',
                'districtName',
                'projectType',
                'wardOptions'
            ));
        }

        $site = Site::with(['stateRelation', 'districtRelation', 'projectRelation', 'vendorRelation', 'engineerRelation'])
            ->findOrFail($id);

        $states = State::all();
        $districts = City::where('state_id', $site->state)->get();
        $projects = Project::all();
        $users = User::all();

        return view('sites.show', compact('site', 'states', 'districts', 'projects', 'users', 'projectType'));
    }

    /**
     * Import poles for a specific site
     */
    public function importPoles(Request $request, string $siteId)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            $site = Streetlight::findOrFail($siteId);
            $streetlightTask = StreetlightTask::where('site_id', $site->id)->first();

            if (!$streetlightTask) {
                return redirect()->back()->withErrors(['error' => 'No task found for this site.']);
            }

            $import = new SitePoleImport($siteId, $streetlightTask->id);
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();
            $importedCount = $import->getImportedCount();

            if (!empty($errors)) {
                $lines = [];
                $lines[] = 'Pole Import Errors - ' . now()->toDateTimeString();
                $lines[] = 'Site ID: ' . $siteId;
                $lines[] = str_repeat('=', 80);

                foreach ($errors as $err) {
                    $lines[] = "Row: " . ($err['row'] ?? 'Unknown');
                    $lines[] = "  Pole Number: " . ($err['pole_number'] ?? '');
                    $lines[] = "  Reason: " . ($err['reason'] ?? 'Unknown error');
                    $lines[] = str_repeat('-', 40);
                }

                $content = implode(PHP_EOL, $lines) . PHP_EOL;
                $disk = Storage::disk('public');
                if (!$disk->exists('import_errors')) {
                    $disk->makeDirectory('import_errors');
                }

                $fileName = 'poles_errors_site_' . $siteId . '_' . time() . '.txt';
                $relativePath = 'import_errors/' . $fileName;
                $disk->put($relativePath, $content);
                $errorFileUrl = $disk->url($relativePath);

                $message = "Imported {$importedCount} pole(s) with " . count($errors) . " error(s).";
                return redirect()->back()
                    ->with('success', $message)
                    ->with('import_errors_url', $errorFileUrl)
                    ->with('import_errors_count', count($errors));
            }

            return redirect()->back()->with('success', "Successfully imported {$importedCount} pole(s).");
        } catch (\Exception $e) {
            Log::error('Pole import failed: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Bulk delete poles
     */
    public function bulkDeletePoles(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'required|integer',
            ]);

            $deletedCount = Pole::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} pole(s) deleted successfully."
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk delete poles failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete poles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download pole import format template
     */
    public function downloadPoleImportFormat(string $siteId)
    {
        try {
            $site = Streetlight::findOrFail($siteId);
            
            $data = [
                [
                    'complete_pole_number' => 'LAK/SWRAMOHA/WARD 10/1',
                    'beneficiary' => 'John Doe',
                    'beneficiary_contact' => '9876543210',
                    'ward_name' => 'Ward 10',
                    'luminary_qr' => 'LUM123456',
                    'battery_qr' => 'BAT123456',
                    'panel_qr' => 'PAN123456',
                    'sim_number' => '9876543210',
                    'lat' => '25.123456',
                    'long' => '85.123456',
                    'date_of_installation' => date('Y-m-d'),
                ]
            ];

            $filename = 'poles_import_format_site_' . $siteId . '_' . date('Y-m-d') . '.xlsx';

            return ExcelHelper::exportToExcel($data, $filename);
        } catch (\Exception $e) {
            Log::error('Failed to download pole import format: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to download import format: ' . $e->getMessage()]);
        }
    }


    public function search(Request $request)
    {
        $search = $request->input('search');
        $sites = Site::with(['stateRelation', 'districtRelation'])
            ->where('breda_sl_no', $search)
            ->orWhere('site_name', 'LIKE', "%{$search}%")
            ->orWhere('location', 'LIKE', "%{$search}%")
            ->orWhereHas('stateRelation', function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%");
            })
            ->orWhereHas('districtRelation', function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%");
            })
            ->limit(10)
            ->get(['id', 'breda_sl_no', 'site_name', 'location', 'state', 'district']);

        return response()->json($sites->map(function ($site) {
            return [
                'id' => $site->id,
                'text' => "{$site->breda_sl_no} - {$site->site_name} ({$site->location}) - " .
                    ($site->stateRelation->name ?? 'N/A') . ", " .
                    ($site->districtRelation->name ?? 'N/A')
            ];
        }));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id, Request $request)
    {
        $projectId = $request->query('project_id');

        if ($projectId) {
            $project = Project::find($projectId);
            if ($project && $project->project_type == 1) {
                $streetlight = Streetlight::findOrFail($id);
                return view('sites.edit', compact('streetlight', 'projectId'));
            }
        }

        $site = Site::findOrFail($id);
        return view('sites.edit', compact('site', 'projectId'));
    }

    public function update(Request $request, string $id)
    {
        try {
            $projectId = $request->query('project_id') ?? $request->input('project_id');

            if ($projectId) {
                $project = Project::find($projectId);
                if ($project && $project->project_type == 1) {
                    $streetlight = Streetlight::findOrFail($id);
                    $streetlight->update($request->only([
                        'task_id',
                        'state',
                        'district',
                        'block',
                        'panchayat',
                        'ward',
                        'mukhiya_contact',
                        'total_poles'
                    ]));
                    return redirect()->route('projects.show', $projectId)
                        ->with('success', 'Streetlight site updated successfully.');
                }
            }

            $site = Site::findOrFail($id);
            $site->update($request->only([
                'task_id',
                'state',
                'district',
                'block',
                'panchayat',
                'ward',
                'mukhiya_contact',
            ]));
            return redirect()->route('sites.show', $site->id)
                ->with('success', 'Site updated successfully.');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            return redirect()->back()
                ->withErrors(['error' => $errorMessage])
                ->withInput();
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'required|integer',
            ]);

            $projectId = $request->query('project_id') ?? $request->input('project_id');
            $deletedCount = 0;

            if ($projectId) {
                $project = Project::find($projectId);
                if ($project && $project->project_type == 1) {
                    // Streetlight sites
                    $deletedCount = Streetlight::whereIn('id', $request->ids)->delete();
                } else {
                    // Regular sites
                    $deletedCount = Site::whereIn('id', $request->ids)->delete();
                }
            } else {
                // Default to regular sites if no project context
                $deletedCount = Site::whereIn('id', $request->ids)->delete();
            }

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} site(s) deleted successfully."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete sites: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id, Request $request)
    {
        try {
            $projectId = $request->query('project_id') ?? $request->input('project_id');

            if ($projectId) {
                $project = Project::find($projectId);
                if ($project && $project->project_type == 1) {
                    $streetlight = Streetlight::findOrFail($id);
                    $streetlight->delete();
                    return redirect()->back()
                        ->with('success', 'Streetlight site deleted successfully.');
                }
            }

            $site = Site::findOrFail($id);
            $site->delete();
            return redirect()->back()
                ->with('success', 'Site deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete site: ' . $e->getMessage());
        }
    }
}
