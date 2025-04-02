<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Streetlight;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class JICRController extends Controller
{

    public function index()
    {
        $districts = Streetlight::select('district')->distinct()->get();
        $project = Project::where('id', 11)->first(); // Changed from 11 to 1 and using first() to get a single project
        return view('jicr.index', compact('districts', 'project'));
    }
    public function getBlocks($district)
    {
        // Fetch blocks based on the selected district
        $blocks = Streetlight::where('district', $district)->select('block')->distinct()->get();
        return response()->json($blocks);
    }
    public function getPanchayats($block)
    {
        // Fetch panchayats based on the selected block
        $panchayats = Streetlight::where('block', $block)->select('panchayat')->distinct()->get();
        return response()->json($panchayats);
    }

    public function generatePDF(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'district' => 'required|string',
            'block' => 'required|string',
            'panchayat' => 'required|string',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);
        // Get the project details
        $project = Project::where('id', 11)->first();

        // Get streetlights based on the selected criteria
        $streetlights = Streetlight::where('district', $request->district)
            ->where('block', $request->block)
            ->where('panchayat', $request->panchayat)
            ->whereBetween('created_at', [$request->from_date, $request->to_date])
            ->get();

        // Prepare data for the view
        $data = [
            'district' => $request->district,
            'block' => $request->block,
            'panchayat' => $request->panchayat,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'project' => $project,
            'streetlights' => $streetlights,
            'showReport' => true
        ];
        Log::info('PDF generation started' . $data);
        // If it's a download request, generate PDF
        if ($request->has('download') && $request->download == 'pdf') {
            $pdf = Pdf::loadView('jicr.show', $data);
            return $pdf->download('jicr_report.pdf');
        }

        // Otherwise show the report in the browser
        $districts = Streetlight::select('district')->distinct()->get();
        $data['districts'] = $districts;

        return view('jicr.index', $data);
    }
}
