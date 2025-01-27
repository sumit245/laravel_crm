<?php

namespace App\Http\Controllers;

use App\Imports\SiteImport;
use App\Models\City;
use App\Models\Project;
use App\Models\Site;
use App\Models\State;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SiteController extends Controller
{

    // Import Excel file for sites
    public function import(Request $request, $projectId)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new SiteImport($projectId), $request->file('file'));
            return redirect()->route('sites.index')->with('success', 'Sites imported successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $sites = Site::all();

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
        return view('sites.create', compact('states', 'projects', 'vendors', 'staffs')); // Pass states to the view
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            $site = Site::create($request->all());
            return redirect()->route('sites.show', $site->id)
                ->with('success', 'Site created successfully.');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            return redirect()->back()
                ->withErrors(['error' => $errorMessage])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $site = Site::with(['stateRelation', 'districtRelation', 'projectRelation', 'vendorRelation', 'engineerRelation'])->findOrFail($id);

        $states    = State::all();
        $districts = City::where('state_id', $site->state)->get(); // Dynamically load districts based on state
        $projects  = Project::all();
        $users     = User::all(); // For vendor and engineer names

        return view('sites.show', compact('site', 'states', 'districts', 'projects', 'users'));
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        Log::info($search);
        $sites = Site::where('site_name', 'LIKE', "%{$search}%")
            ->limit(10) // Limit results to improve performance
            ->get(['id', 'site_name']);
        return response()->json($sites->map(function ($site) {
            return [
                'id' => $site->id,
                'text' => $site->site_name
            ];
        }));
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $site = Site::findOrFail($id);
        return view('sites.edit', compact('site'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        try {
            $site = Site::findOrFail($id);
            $site->update($request->all());
            return redirect()->route('sites.show', $site->id)
                ->with('success', 'Site created successfully.');
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            return redirect()->back()
                ->withErrors(['error' => $errorMessage])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        //
        try {
            $site = Site::findOrFail($id);
            $site->delete();
            return response()->json(['message' => 'Site deleted']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }
}
