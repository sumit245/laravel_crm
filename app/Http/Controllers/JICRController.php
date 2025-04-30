<?php

namespace App\Http\Controllers;

use App\Models\Pole;
use App\Models\Project;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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
    public function getWards($panchayat)
    {
        $wardString = Streetlight::where('panchayat', $panchayat)
            ->pluck('ward')
            ->first(); // Assuming one row per panchayat

        Log::info("Raw ward string: " . $wardString);

        $wardArray = [];

        if ($wardString) {
            $wards = explode(',', $wardString); // Split the string into an array
            foreach ($wards as $ward) {
                $wardArray[] = ['ward' => trim($ward)]; // Clean whitespace
            }
        }

        Log::info($wardArray);

        return response()->json($wardArray);
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
            $normalizedDistrict = strtolower(trim($request->district));
            $normalizedBlock = strtolower(trim($request->block));
            $normalizedPanchayat = strtolower(trim($request->panchayat));

            $streetlight = Streetlight::whereRaw("TRIM(LOWER(district)) = ?", [$normalizedDistrict])
                ->whereRaw("TRIM(LOWER(block)) = ?", [$normalizedBlock])
                ->whereRaw("TRIM(LOWER(panchayat)) = ?", [$normalizedPanchayat])
                ->first();

            // Get streetlights based on the selected criteria
            // $streetlight = Streetlight::whereRaw('LOWER(district) LIKE ?', ['%' . strtolower($request->district) . '%'])
            //     ->whereRaw('LOWER(block) LIKE ?', ['%' . strtolower($request->block) . '%'])
            //     ->whereRaw('LOWER(panchayat) LIKE ?', ['%' . strtolower($request->panchayat) . '%'])
            //     ->first();
            Log::info($streetlight);
            $project = Project::where('id', $streetlight->project_id)->first();


            $assignedTasks = StreetlightTask::where('site_id', $streetlight->id)
                ->with(['engineer', 'vendor', 'manager'])
                ->first();
            $data = [
                'task_id' => $assignedTasks->id,
                'state' => $streetlight->state,
                'district' => $streetlight->district,
                'block' => $streetlight->block,
                'panchayat' => $streetlight->panchayat,
                'ward' => array_map('intval', explode(',', $streetlight->ward)),
                'number_of_surveyed_poles' => $streetlight->number_of_surveyed_poles,
                'number_of_installed_poles' => $streetlight->number_of_installed_poles,
                'total_poles' => $streetlight->total_poles,
                'project_name' => $project->project_name ?? '',
                'project_start_date' => $project->start_date ?? '',
                'agreement_number' => $project->agreement_number,
                'project_capacity' => $project->project_capacity,
                'agreement_date' => $project->agreement_date,
                'project_end_date' => $project->end_date ?? '',
                'work_order_number' => $project->work_order_number ?? '',

            ];
            if ($assignedTasks) {
                $data['engineer_name'] = optional($assignedTasks->engineer)->firstName . ' ' . optional($assignedTasks->engineer)->lastName;
                $data['engineer_image'] = optional($assignedTasks->engineer)->image;
                $data['engineer_contact'] = optional($assignedTasks->engineer)->contactNo;

                $data['vendor_name'] = optional($assignedTasks->vendor)->firstName . ' ' . optional($assignedTasks->vendor)->lastName;
                $data['vendor_image'] = optional($assignedTasks->vendor)->image;
                $data['vendor_contact'] = optional($assignedTasks->vendor)->contactNo;

                $data['project_manager_name'] = optional($assignedTasks->manager)->firstName . ' ' . optional($assignedTasks->manager)->lastName;
                $data['project_manager_image'] = optional($assignedTasks->manager)->image;
                $data['project_manager_contact'] = optional($assignedTasks->manager)->contactNo;
            }

            $data['poles'] = Pole::where('task_id', $assignedTasks->id)
                ->whereBetween('updated_at', [$request->from_date, $request->to_date])
                ->get()
                ->map(function ($pole) use ($streetlight) {
                    return [
                        'district' => $streetlight->district,
                        'block' => $streetlight->block,
                        'panchayat' => $streetlight->panchayat,
                        'solar_panel_no' => $pole->panel_qr,
                        'battery_no' => $pole->battery_qr,
                        'luminary_no' => $pole->luminary_qr,
                        'sim_no' => $pole->sim_number,
                        'complete_pole_number' => $pole->complete_pole_number,
                        'ward_no' => $pole->ward_name,
                        'beneficiary' => $pole->beneficiary,
                        'latitude' => $pole->lat,
                        'longitude' => $pole->lng,
                        'date_of_installation' => Carbon::parse($pole->updated_at)->format('d-m-Y'),
                    ];
                })
                ->sortBy('ward_no') // Sorting by ward_no in ascending order
                ->values(); // Reindex the collection
            $data['showReport'] = true; // Flag to show the report

            // If it's a download request, generate PDF
            if ($request->has('download') && $request->download == 'pdf') {
                $pdf = Pdf::loadView('jicr.show', $data);
                return $pdf->download('jicr_report.pdf');
            }
            // Otherwise show the report in the browser
            $districts = Streetlight::select('district')->distinct()->get();
            $data['districts'] = $districts;

            return view('jicr.index', [
                'districts' => $districts,
                'showReport' => true,
                'data' => $data, // Make sure this is either an object or associative array
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
