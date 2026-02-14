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
        $projectId = env('JICR_DEFAULT_PROJECT_ID', null);
        $project = $projectId ? Project::find($projectId) : Project::first();
        return view('jicr.index', compact('districts', 'project'));
    }
    public function getBlocks(Request $request, $district)
    {
        // Fetch blocks based on the selected district
        $query = Streetlight::where('district', $district)->select('block')->distinct();

        if ($request->has('project_id')) {
            $query->where('project_id', $request->input('project_id'));
        }

        $blocks = $query->get();

        // Fetch district code
        $districtCodeQuery = Streetlight::where('district', $district);
        if ($request->has('project_id')) {
            $districtCodeQuery->where('project_id', $request->input('project_id'));
        }
        // Prefer non-null codes if available, otherwise take first
        $districtCode = $districtCodeQuery->whereNotNull('district_code')
            ->where('district_code', '!=', '')
            ->value('district_code');

        // If still null, check if any record exists even without code
        if (!$districtCode) {
            $districtCode = $districtCodeQuery->value('district_code');
        }

        return response()->json([
            'blocks' => $blocks,
            'district_code' => $districtCode
        ]);
    }

    public function getPanchayats(Request $request, $block)
    {
        // Fetch panchayats based on the selected block
        $query = Streetlight::where('block', $block)->select('panchayat')->distinct();

        if ($request->has('project_id')) {
            $query->where('project_id', $request->input('project_id'));
        }
        if ($request->has('district')) {
            $query->where('district', $request->input('district'));
        }

        $panchayats = $query->get();

        // Fetch block code
        $blockCodeQuery = Streetlight::where('block', $block);
        if ($request->has('project_id')) {
            $blockCodeQuery->where('project_id', $request->input('project_id'));
        }
        if ($request->has('district')) {
            $blockCodeQuery->where('district', $request->input('district'));
        }
        $blockCode = $blockCodeQuery->whereNotNull('block_code')
            ->where('block_code', '!=', '')
            ->value('block_code');

        if (!$blockCode) {
            $blockCode = $blockCodeQuery->value('block_code');
        }

        return response()->json([
            'panchayats' => $panchayats,
            'block_code' => $blockCode
        ]);
    }
    public function getWards(Request $request, $panchayat)
    {
        $query = Streetlight::where('panchayat', $panchayat);

        if ($request->has('project_id')) {
            $query->where('project_id', $request->input('project_id'));
        }
        if ($request->has('district')) {
            $query->where('district', $request->input('district'));
        }
        if ($request->has('block')) {
            $query->where('block', $request->input('block'));
        }

        $wardString = $query->pluck('ward')->first(); // Assuming one row per panchayat

        Log::info("Raw ward string: " . $wardString);

        $wardArray = [];

        if ($wardString) {
            $wards = explode(',', $wardString); // Split the string into an array
            foreach ($wards as $ward) {
                $wardArray[] = ['ward' => trim($ward)]; // Clean whitespace
            }
        }

        // Fetch panchayat code
        $panchayatCodeQuery = clone $query;
        $panchayatCode = $panchayatCodeQuery->whereNotNull('panchayat_code')
            ->where('panchayat_code', '!=', '')
            ->value('panchayat_code');

        if (!$panchayatCode) {
            // If explicit non-empty code not found, try any value even if null/empty just in case
            $panchayatCode = $query->value('panchayat_code');
        }

        Log::info("Wards for $panchayat", ['wards' => $wardArray]);

        return response()->json([
            'wards' => $wardArray,
            'panchayat_code' => $panchayatCode
        ]);
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

            // Check if streetlight exists
            if (!$streetlight) {
                return back()->with('error', 'No streetlight data found for the selected panchayat.');
            }

            Log::info($streetlight);
            $project = Project::where('id', $streetlight->project_id)->first();

            $assignedTasks = StreetlightTask::where('site_id', $streetlight->id)
                ->with(['engineer', 'vendor', 'manager'])
                ->first();

            // Build data array with null-safe defaults
            $wardArray = [];
            if (!empty($streetlight->ward)) {
                $wardArray = array_map('intval', explode(',', $streetlight->ward));
            }

            $data = [
                'task_id' => $assignedTasks->id ?? null,
                'state' => $streetlight->state ?? '',
                'district' => $streetlight->district ?? '',
                'block' => $streetlight->block ?? '',
                'panchayat' => $streetlight->panchayat ?? '',
                'ward' => $wardArray,
                'number_of_surveyed_poles' => $streetlight->number_of_surveyed_poles ?? 0,
                'number_of_installed_poles' => $streetlight->number_of_installed_poles ?? 0,
                'total_poles' => $streetlight->total_poles ?? 0,
                'project_name' => $project->project_name ?? '',
                'project_start_date' => $project->start_date ?? '',
                'agreement_number' => $project->agreement_number ?? '',
                'project_capacity' => $project->project_capacity ?? '',
                'agreement_date' => $project->agreement_date ?? '',
                'project_end_date' => $project->end_date ?? '',
                'work_order_number' => $project->work_order_number ?? '',
            ];
            // Set default values for engineer, vendor, and manager
            $data['engineer_name'] = '';
            $data['engineer_image'] = '';
            $data['engineer_contact'] = '';
            $data['vendor_name'] = '';
            $data['vendor_image'] = '';
            $data['vendor_contact'] = '';
            $data['project_manager_name'] = '';
            $data['project_manager_image'] = '';
            $data['project_manager_contact'] = '';

            if ($assignedTasks) {
                $data['engineer_name'] = optional($assignedTasks->engineer)->firstName . ' ' . optional($assignedTasks->engineer)->lastName ?? '';
                $data['engineer_image'] = optional($assignedTasks->engineer)->image ?? '';
                $data['engineer_contact'] = optional($assignedTasks->engineer)->contactNo ?? '';

                $data['vendor_name'] = optional($assignedTasks->vendor)->firstName . ' ' . optional($assignedTasks->vendor)->lastName ?? '';
                $data['vendor_image'] = optional($assignedTasks->vendor)->image ?? '';
                $data['vendor_contact'] = optional($assignedTasks->vendor)->contactNo ?? '';

                $data['project_manager_name'] = optional($assignedTasks->manager)->firstName . ' ' . optional($assignedTasks->manager)->lastName ?? '';
                $data['project_manager_image'] = optional($assignedTasks->manager)->image ?? '';
                $data['project_manager_contact'] = optional($assignedTasks->manager)->contactNo ?? '';
            }

            // Query poles only if task exists, otherwise set empty array
            if ($assignedTasks) {
                $data['poles'] = Pole::where('task_id', $assignedTasks->id)
                    ->where('isInstallationDone', true)
                    ->whereBetween('updated_at', [$request->from_date, $request->to_date])
                    ->get()
                    ->map(function ($pole) use ($streetlight) {
                        $latitude = $pole->lat;
                        $longitude = $pole->lng;

                        $formattedLatitude = is_numeric($latitude)
                            ? number_format((float) $latitude, 3, '.', '')
                            : ($latitude ?? '');

                        $formattedLongitude = is_numeric($longitude)
                            ? number_format((float) $longitude, 3, '.', '')
                            : ($longitude ?? '');

                        return [
                            'district' => $streetlight->district ?? '',
                            'block' => $streetlight->block ?? '',
                            'panchayat' => $streetlight->panchayat ?? '',
                            'solar_panel_no' => $pole->panel_qr ?? '',
                            'battery_no' => $pole->battery_qr ?? '',
                            'luminary_no' => $pole->luminary_qr ?? '',
                            'sim_no' => $pole->sim_number ?? '',
                            'complete_pole_number' => $pole->complete_pole_number ?? '',
                            'ward_no' => $pole->ward_name ?? '',
                            'beneficiary' => $pole->beneficiary ?? '',
                            'latitude' => $formattedLatitude,
                            'longitude' => $formattedLongitude,
                            'date_of_installation' => Carbon::parse($pole->updated_at)->format('d-m-Y'),
                        ];
                    })
                    ->sortBy('ward_no') // Sorting by ward_no in ascending order
                    ->values(); // Reindex the collection
            } else {
                $data['poles'] = [];
            }
            $data['showReport'] = true; // Flag to show the report

            // Determine success message based on whether poles were found
            $polesCount = count($data['poles']);
            $successMessage = $polesCount > 0
                ? 'JICR generated successfully with ' . $polesCount . ' pole(s).'
                : 'JICR generated successfully. No poles found for the selected criteria.';

            // If it's a download request, generate PDF
            if ($request->has('download') && $request->download == 'pdf') {
                $pdf = Pdf::loadView('jicr.pdf', ['data' => $data]);
                // Note: PDF download response is a file download, which itself indicates success
                return $pdf->download('jicr_report.pdf');
            }
            // Otherwise show the report in the browser
            $districts = Streetlight::select('district')->distinct()->get();
            $data['districts'] = $districts;

            return view('jicr.index', [
                'districts' => $districts,
                'showReport' => true,
                'data' => $data, // Make sure this is either an object or associative array
            ])->with('success', $successMessage);
        } catch (\Exception $e) {
            Log::error('JICR Generation Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'An error occurred while generating JICR: ' . $e->getMessage());
        }
    }
}
