<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Conveyance;
use App\Models\dailyfare;
use App\Models\Tada;
use App\Models\travelfare;
use App\Models\User;
use App\Models\UserCategory;
use App\Models\Vehicle;
use DB;
use Illuminate\Http\Request;
use Storage;

class ConveyanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            //code...
            $tadas = Tada::select([
                'meeting_visit',
                'user_id',
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
                'message' => 'Tada data fetched successfully',
                'data' => $tadas
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch tada data',
                'error' => $th->getMessage()
            ]);
        }
       
    }

    // Conveyance Index
    public function indexConveyance()
    {
        try {
            $conveyances = Conveyance::select([
                'from',
                'to',
                'kilometer',
                'created_at',
                'time',
                'vehicle_category',
                'user_id',
            ])->get();
            return response()->json([
                'status' => true,
                'message' => 'Conveyance data fetched successfully',
                'data' => $conveyances
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch conveyance data',
                'error' => $e->getMessage()
            ]);
        }
        
    }

    public function checkPrice(){
        try {
            //code...
            $tierprice = City::whereNotNull('tier')->get();
            $userprice = UserCategory::all();
            return response()->json([
                'status' => true,
                'message' => 'Tier price fetched successfully',
                'data' => $tierprice, $userprice
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch tier price',
                'error' => $th->getMessage()
            ]);
        }
    }

    public function getVehicles(){
        $vehicles = Vehicle::get();
        return response()->json($vehicles);
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
    try {
        //code...
         $dataTada = $request->validate([
            'user_id' => 'required|integer',
            'visit_approve' => 'nullable|string|max:50',
            'objective_tour' => 'nullable|string|max:255',
            'meeting_visit' => 'nullable|string|max:255',
            'outcome_achieve' => 'nullable|string|max:255',
            // 'Desgination' => 'required|string|max:100',
            'start_journey_pnr' => 'nullable|string|max:255',
            'start_journey' => 'date',
            'start_journey_time' => 'nullable|date_format:H:i:s',
            'end_journey' => 'nullable|date',
            'end_journey_time' => 'nullable|date_format:H:i:s',
            'end_journey_pnr' => 'nullable|string|max:255',
            'transport' => 'string|max:50',
            // 'start_journey_pnr.*' => 'file|mimes:pdf', // validate each file
            'from_city' => 'string|max:100',
            'to_city' => 'string|max:100',
            // 'end_journey_pnr.*' => 'file|mimes:pdf', // validate each file
            'total_km' => 'integer',
            'rate_per_km' => 'integer',
            'Rent' => 'integer',
            'vehicle_no' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'description_category' => 'nullable|string',
            'otherexpense' => 'nullable|array'
            
        ]);
        // $datatravelfare = $request->validate([
        //     'from' => 'required|integer',
        //     'to' => 'nullable|string|max:100',
        //     'departure_date' => 'nullable|date',
        //     'departure_time' => 'nullable|date_format:H:i:s',
        //     'arrival_date' => 'nullable|date',
        //     'arrival_time' => 'nullable|date_format:H:i:s',
        //     'modeoftravel' => 'nullable|string|max:100',
        //     'add_total_km' => 'nullable|numeric',
        //     'add_rate_per_km' => 'nullable|numeric',
        //     'add_rent' => 'nullable|numeric',
        //     'add_vehicle_no' => 'nullable|string|max:100',
        //     'amount' => 'nullable|numeric',
        // ]);
        // $datadailyfare = $request->validate([
        //     'place' => 'nullable|string|max:100',
        //     'HotelBillNo' => 'nullable|string|max:150',
        //     'date_of_stay' => 'nullable|date',
        //     'amount' => 'nullable|numeric',
        // ]);
        
        // $tada = Tada::create($dataTada);
        // if (!$tada) {
        //     throw new \Exception('Failed to create Tada.');
        // }
        // $datatravelfare['tada_id'] = $tada->id;
        // $datadailyfare['tada_id'] = $tada->id;
        // $travelfare = travelfare::create($datatravelfare);
        // $dailyfare = dailyfare::create($datadailyfare);
        // if (!$travelfare || !$dailyfare) {
        //     throw new \Exception('Failed to create Travelfare or Dailyfare.');
        // }
        // DB::commit();
        // return response()->json([
        //     'tada' => $tada,
        //     'travelfare' => $travelfare,
        //     'dailyfare' => $dailyfare
        // ], 201);

        
        $request->validate([
            'travelfare' => 'required|array',
            'travelfare.*.from' => 'required|integer',
            'travelfare.*.to' => 'nullable|string|max:100',
            'travelfare.*.departure_date' => 'nullable|date',
            'travelfare.*.departure_time' => 'nullable|date_format:H:i:s',
            'travelfare.*.arrival_date' => 'nullable|date',
            'travelfare.*.arrival_time' => 'nullable|date_format:H:i:s',
            'travelfare.*.modeoftravel' => 'nullable|string|max:100',
            'travelfare.*.add_total_km' => 'nullable|numeric',
            'travelfare.*.add_rate_per_km' => 'nullable|numeric',
            'travelfare.*.add_rent' => 'nullable|numeric',
            'travelfare.*.add_vehicle_no' => 'nullable|string|max:100',
            'travelfare.*.amount' => 'nullable|numeric',

            'dailyfare' => 'required|array',
            'dailyfare.*.place' => 'nullable|string|max:100',
            'dailyfare.*.HotelBillNo' => 'nullable|string|max:150',
            'dailyfare.*.date_of_stay' => 'nullable|date',
            'dailyfare.*.amount' => 'nullable|numeric',
        ]);
        DB::beginTransaction();
        $tada = Tada::create($dataTada);

        if (!$tada) {
            throw new \Exception('Failed to create Tada.');
        }

        foreach ($request->travelfare as $travelData) {
            $travelData['tada_id'] = $tada->id;
            if (!Travelfare::create($travelData)) {
                throw new \Exception('Failed to create Travelfare record.');
            }
        }

        foreach ($request->dailyfare as $dailyData) {
            $dailyData['tada_id'] = $tada->id;
            if (!Dailyfare::create($dailyData)) {
                throw new \Exception('Failed to create Dailyfare record.');
            }
        }

        DB::commit();
        return response()->json([
            'status' => true,
            'message' => 'TADA and related records created successfully.',
            'tada' => $tada,
            'travelfare' => $request->travelfare,
            'dailyfare' => $request->dailyfare,
        ], 201);

    } catch (\Throwable $th) {
        //throw $th;
        return response()->json([
            'status' => false,
            'message' => 'Failed to create tada data',
            'error' => $th->getMessage()
        ]);
    }

    
       
    }

    public function storeConveyance(Request $request)
{
    try {
        $data = $request->validate([
            'from'             => 'string|max:100',
            'to'               => 'string|max:100',
            'kilometer'        => 'integer',
            'time'             => 'string|max:50',
            'vehicle_category' => 'integer',
            'user_id'          => 'integer',
            'amount'           => 'nullable|numeric'
            // 'image'            => 'nullable|image|mimes:jpeg,png,jpg|max:4048',
        ]);

        // Upload image to S3 if present
        // if ($request->hasFile('image') && $request->file('image')->isValid()) {
        //     $file = $request->file('image');

        //     $uploadedImage = $this->uploadToS3($file); // No folder override

        //     if (!$uploadedImage) {
        //         throw new \Exception('Failed to upload image to S3');
        //     }

        //     $data['image'] = $uploadedImage;
        // }

        $conveyance = Conveyance::create($data);

        return response()->json($conveyance, 201);

    } catch (\Throwable $th) {
        \Log::error('Conveyance creation error: ' . $th->getMessage());

        return response()->json([
            'status'  => false,
            'message' => 'Failed to create conveyance',
            'error'   => $th->getMessage()
        ], 500);
    }
}




    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        try {
            $tada = Tada::where('user_id', $id)->get(); // or firstOrFail()

            if (!$tada) {
                return response()->json([
                    'status' => false,
                    'message' => 'No tada record found for this user_id.'
                ], 404);
            }
            foreach ($tada as $tadaItem) {
                $travelfare = travelfare::where('tada_id', $tadaItem->id)->first();
                $dailyfare = dailyfare::where('tada_id', $tadaItem->id)->first();
                
                    if (!$travelfare) {
                        \Log::info("Travelfare missing for tada_id: " . $tadaItem->id);
                    }
                    if (!$dailyfare) {
                        \Log::info("Dailyfare missing for tada_id: " . $tadaItem->id);
                    }

                if (!$travelfare || !$dailyfare) {
                    continue;
                }
                $result[] = [
                    'tada' => $tadaItem,
                    'travelfare' => $travelfare,
                    'dailyfare' => $dailyfare,
                ];
            }
            if (empty($result)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No complete TADA records found with both travelfare and dailyfare'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'TADA records fetched successfully',
                'data' => $result
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch tada',
                'error' => $th->getMessage()
            ]);
        }
    }

    public function showConveyance(string $id){
        try {
            $conveyance = Conveyance::where('user_id', $id)->get();
            if (!$conveyance) {
                return response()->json([
                    'status' => false,
                    'message' => 'Conveyance not found'
                ], 404);
            }
            return response()->json([
                'status' => true,
                'message' => 'Conveyance fetched successfully',
                'data' => $conveyance
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch conveyance',
                'error' => $th->getMessage()
            ]);
        }
    }

    public function getUserCategoryVehicle(){
        try {
            //code...
            $usercategory = UserCategory::all();
            return response()->json([
                'status' => true,
                'message' => 'User Category fetched successfully',
                'data' => $usercategory
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch user category',
                'error' => $th->getMessage()
            ]);
        }
        
    }

    public function getUserCategory(Int $id){
        try {
            //code...
            $usercategory = User::where('id', $id)->pluck('category');
            return response()->json([
                'status' => true,
                'message' => 'User Category fetched successfully',
                'data' => $usercategory
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch user category',
                'error' => $th->getMessage()
            ]);
        }
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
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
