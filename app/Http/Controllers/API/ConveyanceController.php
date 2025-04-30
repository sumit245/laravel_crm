<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conveyance;
use Illuminate\Http\Request;

class ConveyanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $conveyances = Conveyance::select([
            'name',
            'employee_id as Employee Id',
            'meeting_visit',
            'department',
            'start_journey as start_date',
            'end_journey as end_date',
            'start_journey_pnr',
            'end_journey_pnr',
            'visit_approve',
            'transport',
            'objective_tour',
            'meeting_visit',
            'outcome_achieve',
            'from_city as source',
            'to_city as destination',
            'category as categories',
            'description_category as descriptions',
            'total_km',
            'rate_per_km as km_rate',
            'Rent as rent',
            'vehicle_no as vehicle_number',
        ])->get();

        return response()->json([
            'status' => true,
            'message' => 'Conveyance data fetched successfully',
            'data' => $conveyances
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'employee_id' => 'required|string|max:100',
            'visit_approve' => 'nullable|string|max:50',
            'objective_tour' => 'nullable|string|max:255',
            'meeting_visit' => 'nullable|string|max:255',
            'outcome_achieve' => 'nullable|string|max:255',
            'Desgination' => 'required|string|max:100',
            'start_journey' => 'required|date',
            'end_journey' => 'required|date',
            'transport' => 'required|string|max:50',
            'start_journey_pnr' => 'nullable|array',
            // 'start_journey_pnr.*' => 'file|mimes:pdf', // validate each file
            'from_city' => 'required|string|max:100',
            'to_city' => 'required|string|max:100',
            'end_journey_pnr' => 'nullable|array',
            // 'end_journey_pnr.*' => 'file|mimes:pdf', // validate each file
            'total_km' => 'required|integer',
            'rate_per_km' => 'required|integer',
            'Rent' => 'required|integer',
            'vehicle_no' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'description_category' => 'nullable|string',
            'pickup_date' => 'required|date',
        ]);

        // Handle start_journey_pnr files
    // if ($request->hasFile('start_journey_pnr')) {
    //     $startFiles = [];
    //     foreach ($request->file('start_journey_pnr') as $file) {
    //         $startFiles[] = $file->store('pnrs/start', 'public');
    //     }
    //     $data['start_journey_pnr'] = $startFiles;
    // }

    // Handle end_journey_pnr files
    // if ($request->hasFile('end_journey_pnr')) {
    //     $endFiles = [];
    //     foreach ($request->file('end_journey_pnr') as $file) {
    //         $endFiles[] = $file->store('pnrs/end', 'public');
    //     }
    //     $data['end_journey_pnr'] = $endFiles;
    // }

        $conveyance = Conveyance::create($data);

        return response()->json($conveyance, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
