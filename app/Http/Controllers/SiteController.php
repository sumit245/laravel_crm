<?php

namespace App\Http\Controllers;

use App\Imports\SiteImport;
use App\Imports\StreetlightImport;
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
use Maatwebsite\Excel\Facades\Excel;

class SiteController extends Controller
{
    // Import Excel file for sites
    public function import(Request $request, $projectId)
    {
        Log::info('Import method triggered for project ID: ' . $projectId);
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        Log::info('Uploaded file:', ['file' => $request->file('file')]);

        $project = Project::find($request->project_id);
        if (!$project) {
            Log::error('Project not found for ID: ' . $projectId);
            return back()->with('error', 'Project not found.');
        }

        try {
            if ($project->project_type == 1) {
                Log::info('Importing Streetlight Data...');
                Excel::import(new StreetlightImport($projectId), $request->file('file'));
                Log::info('Streetlight Import Completed.');
                return back()->with('success', 'Streetlight data imported successfully.');
            } else {
                Log::info('Importing Rooftop Data...');
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

        // Get project_id from route parameter or query string
        $projectId = $projectId ?? $request->query('project_id');

        if ($projectId) {
            $project = Project::find($projectId);
            if ($project) {
                $projectType = $project->project_type;
            }
        }

        $states = State::all();
        $projects = Project::all();
        $vendors = User::where('role', 3)->get();
        $staffs = User::whereIn('role', [1, 2])->get();

        return view('sites.create', compact('states', 'projects', 'vendors', 'staffs', 'project', 'projectType'));
    }

    /**
     * Generate task_id for streetlight sites based on district prefix
     */
    private function generateTaskId($district)
    {
        $districtPrefix = strtoupper(substr($district, 0, 3)); // Extract first 3 letters

        // Find the last task_id with this district prefix
        $lastTask = Streetlight::where('task_id', 'LIKE', "{$districtPrefix}%")
            ->orderBy('task_id', 'desc')
            ->first();

        if ($lastTask) {
            // Extract numeric part and increment
            preg_match('/(\d+)$/', $lastTask->task_id, $matches);
            $counter = isset($matches[1]) ? (int) $matches[1] + 1 : 1;
        } else {
            $counter = 1;
        }

        // Generate task_id in format: DISTRICT_PREFIX + 3-digit number
        return sprintf('%s%03d', $districtPrefix, $counter);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            Log::info('Request received for create site', $request->all());

            // Get project to determine project type
            $projectId = $request->input('project_id');
            $project = Project::find($projectId);

            if (!$project) {
                return redirect()->back()
                    ->withErrors(['error' => 'Project not found.'])
                    ->withInput();
            }

            // Handle streetlight projects (project_type == 1)
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

                // Generate task_id based on district
                $validatedData['task_id'] = $this->generateTaskId($validatedData['district']);

                $streetlight = Streetlight::create($validatedData);

                Log::info('Streetlight site created successfully', [
                    'streetlight_id' => $streetlight->id,
                    'task_id' => $streetlight->task_id
                ]);

                return redirect()->route('projects.show', $projectId)
                    ->with('success', 'Streetlight site created successfully.');
            } else {
                // Handle rooftop projects (project_type == 0)
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

                Log::info('Site created successfully', ['site_id' => $site->id, 'site_name' => $site->site_name]);

                return redirect()->route('sites.show', $site->id)
                    ->with('success', 'Site created successfully.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Site creation failed - Validation errors', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Site creation failed - Exception', [
                'error' => $e->getMessage(),
                'input' => $request->all()
            ]);
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
            // Step 1. Find the streetlight that will return id, district, state, block, panchayat, ward, number_of_surveyed_poles and number_of_installed_poles 
            $site = Streetlight::with('streetlightTasks')->findOrFail($id);

            // Step 2. Use the id of streetlight to find streetlight_task allotted corresponding to the site by column site_id
            // Return engineer_id, vendor_id, manager_id, start_date, end_date, billed with names via eager loading

            $streetlightTask = StreetlightTask::with(['engineer', 'vendor', 'manager'])
                ->where('site_id', $site->id)
                ->first();

            // Step 3: Use the id of streetlight_task to find streelight_poles corresponding to task_id
            // Group poles according to ward_name so that frontend can filter poles specific to a ward

            $poles = Pole::where('task_id', $streetlightTask->id ?? null)
                ->get();
            // ->groupBy('ward_name');

            Log::info($poles);

            // Prepare readable engineer, vendor, manager names
            $engineerName = optional($streetlightTask?->engineer)->firstName . " " . optional($streetlightTask?->engineer)->lastName ?? 'N/A';

            $vendorName = optional($streetlightTask?->vendor)->name ?? 'N/A';
            $managerName = optional($streetlightTask?->manager)->firstName . " " . optional($streetlightTask?->manager)->lastName ?? 'N/A';

            // Prepare state and district names if you have relationships, else keep as stored
            $stateName = $site->state;
            $districtName = $site->district;



            // Return to view with all required data
            return view('sites.show', compact(
                'site',
                'streetlightTask',
                'poles',
                'engineerName',
                'vendorName',
                'managerName',
                'stateName',
                'districtName',
                'projectType'
            ));
        }

        // For non-streetlight projects (default site)
        $site = Site::with(['stateRelation', 'districtRelation', 'projectRelation', 'vendorRelation', 'engineerRelation'])
            ->findOrFail($id);

        $states = State::all();
        $districts = City::where('state_id', $site->state)->get(); // Dynamically load districts based on state
        $projects = Project::all();
        $users = User::all(); // For vendor and engineer names

        return view('sites.show', compact('site', 'states', 'districts', 'projects', 'users', 'projectType'));
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
                // Streetlight project - use Streetlight model
                $streetlight = Streetlight::findOrFail($id);
                return view('sites.edit', compact('streetlight', 'projectId'));
            }
        }

        // Default to regular Site model (rooftop projects)
        $site = Site::findOrFail($id);
        return view('sites.edit', compact('site', 'projectId'));
    }

    public function update(Request $request, string $id)
    {
        try {
            // Determine project type from project_id
            $projectId = $request->query('project_id') ?? $request->input('project_id');

            if ($projectId) {
                $project = Project::find($projectId);
                if ($project && $project->project_type == 1) {
                    // Streetlight project - use Streetlight model
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

            // Default to regular Site model (rooftop projects)
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

    public function destroy(string $id, Request $request)
    {
        try {
            // Determine project type from project_id
            $projectId = $request->query('project_id') ?? $request->input('project_id');

            if ($projectId) {
                $project = Project::find($projectId);
                if ($project && $project->project_type == 1) {
                    // Streetlight project - use Streetlight model
                    $streetlight = Streetlight::findOrFail($id);
                    $streetlight->delete();
                    return redirect()->back()
                        ->with('success', 'Streetlight site deleted successfully.');
                }
            }

            // Default to regular Site model (rooftop projects)
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
