<?php

namespace App\Http\Controllers;

use App\Imports\StreetlightPoleImport;
use Illuminate\Http\Request;
use App\Models\Streetlight;
use App\Models\Project;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class DeviceController extends Controller
{
    public function index()
    {
        $districts = Streetlight::select('district')->distinct()->get();
        $project = Project::where('id', 11)->first(); // Changed from 11 to 1 and using first() to get a single project
        return view('poles.index', compact('districts', 'project'));
    }
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);
        try {
            Log::info('Importing file: ' . $request->file('file')->getClientOriginalName());
            Excel::import(new StreetlightPoleImport, $request->file('file'));
            Log::info('Imported file: ' . $request->file('file')->getClientOriginalName());
            return back()->with('success', 'Pole data imported successfully.');
        } catch (\Exception $e) {
            Log::error('Error importing file: ' . $e->getMessage());
            return back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }
}
