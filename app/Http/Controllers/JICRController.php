<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
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
        try {
            // Validate the request
            $validated = $request->validate([
                'district' => 'required|string',
                'block' => 'required|string',
                'panchayat' => 'required|string',
                'from_date' => 'required|date',
                'to_date' => 'required|date|after_or_equal:from_date',
            ]);

            // Get streetlights based on the selected criteria
            $streetlight = Streetlight::whereRaw('LOWER(district) LIKE ?', ['%' . strtolower($request->district) . '%'])
                ->whereRaw('LOWER(block) LIKE ?', ['%' . strtolower($request->block) . '%'])
                ->whereRaw('LOWER(panchayat) LIKE ?', ['%' . strtolower($request->panchayat) . '%'])
                ->first();
            $project = Project::where('id', $streetlight->project_id)->first();


            $assignedTasks = StreetlightTask::where('site_id', $streetlight->id)
                ->with(['engineer', 'vendor', 'manager'])
                ->get();
            $flatTasks = $assignedTasks->map(function ($task) {
                return [
                    'task_id' => $task->id,
                    'project_id' => $task->project_id,
                    'status' => $task->status,
                    'start_date' => $task->start_date,
                    'end_date' => $task->end_date,
                    'engineer_name' => optional($task->engineer)->firstName . ' ' . optional($task->engineer)->lastName,
                    'engineer_image' => optional($task->engineer)->image,
                    'vendor_name' => optional($task->vendor)->firstName . ' ' . optional($task->vendor)->lastName,
                    'vendor_image' => optional($task->vendor)->image,
                    'manager_name' => optional($task->manager)->firstName . ' ' . optional($task->manager)->lastName,
                    'manager_image' => optional($task->manager)->image,
                    'contact_engineer' => optional($task->engineer)->contactNo,
                    'contact_vendor' => optional($task->vendor)->contactNo,
                    'contact_manager' => optional($task->manager)->contactNo
                ];
            });

            // Prepare data for the view
            $data = [
                'task_id' => $streetlight->task_id,
                'state' => $streetlight->state,
                'district' => $streetlight->district,
                'block' => $streetlight->block,
                'panchayat' => $streetlight->panchayat,
                'ward' => $streetlight->ward,
                'number_of_surveyed_poles' => $streetlight->number_of_surveyed_poles,
                'number_of_installed_poles' => $streetlight->number_of_installed_poles,
                'total_poles' => $streetlight->total_poles,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'tasks' => $flatTasks,
                'project' => $project,
                'showReport' => true,
            ];
            Log::info($data);

            // If it's a download request, generate PDF
            if ($request->has('download') && $request->download == 'pdf') {
                $pdf = Pdf::loadView('jicr.show', $data);
                return $pdf->download('jicr_report.pdf');
            }
            // Otherwise show the report in the browser
            $districts = Streetlight::select('district')->distinct()->get();
            $data['districts'] = $districts;

            return view('jicr.index', $data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
