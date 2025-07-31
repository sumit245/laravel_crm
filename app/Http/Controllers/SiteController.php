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
    public function create()
    {
        $states   = State::all();
        $projects = Project::all();
        $vendors  = User::where('role', 3)->get();
        $staffs   = User::whereIn('role', [1, 2])->get();
        return view('sites.create', compact('states', 'projects', 'vendors', 'staffs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            Log::info('Request received for create site', $request->all());
            
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

            $vendorName   = optional($streetlightTask?->vendor)->name ?? 'N/A';
            $managerName  = optional($streetlightTask?->manager)->firstName . " " . optional($streetlightTask?->manager)->lastName ?? 'N/A';

            // Prepare state and district names if you have relationships, else keep as stored
            $stateName    = $site->state;
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

        $states    = State::all();
        $districts = City::where('state_id', $site->state)->get(); // Dynamically load districts based on state
        $projects  = Project::all();
        $users     = User::all(); // For vendor and engineer names

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
        if ($projectId == 11) {
            $streetlight = Streetlight::findOrFail($id);
            return view('sites.edit', compact('streetlight', 'projectId'));
        }

        $site = Site::findOrFail($id);
        return view('sites.edit', compact('site', 'projectId'));
    }

    public function update(Request $request, string $id)
    {
        try {
            if ($request->project_id == 11) {
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
                return redirect()->route('projects.show', $request->project_id)
                    ->with('success', 'Streetlight site updated successfully.');
            } else {
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
            }
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
            if ($request->project_id == 11) {
                $streetlight = Streetlight::findOrFail($id);
                $streetlight->delete();
                return redirect()->back()
                    ->with('success', 'Streetlight site deleted successfully.');
            } else {
                $site = Site::findOrFail($id);
                $site->delete();
                return response()->json(['message' => 'Site deleted']);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
