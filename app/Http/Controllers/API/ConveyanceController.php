<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Conveyance;
use App\Models\dailyfare;
use App\Models\HotelExpense;
use App\Models\Journey;
use App\Models\Tada;
use App\Models\travelfare;
use App\Models\User;
use App\Models\UserCategory;
use App\Models\Vehicle;
use DB;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB as FacadesDB;

use Log;

use phpseclib3\Math\BinaryField\Integer;
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
            $tadas = Tada::with(['journey', 'hotelExpense'])->get();

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
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch conveyance data',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function checkPrice()
    {
        try {
            //code...
            $tierprice = City::whereNotNull('tier')->get();
            $userprice = UserCategory::all();
            return response()->json([
                'status' => true,
                'message' => 'Tier price fetched successfully',
                'data' => $tierprice,
                $userprice
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

    public function getVehicles($id)
    {
        $vehicle = Vehicle::where('id', $id)->get();
        return response()->json($vehicle);
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
            // Step 1: Create main TADA entry
            $tada = Tada::create([
                'user_id' => $request->user_id,
                'visiting_to' => $request->visiting_to,
                'purpose_of_visit' => $request->purpose_of_visit,
                'outcome_achieved' => $request->outcome_achieved,
                'date_of_departure' => date('Y-m-d H:i:s', strtotime($request->date_of_departure)),
                'date_of_return' => date('Y-m-d H:i:s', strtotime($request->date_of_return)),
                'miscellaneous' => json_encode($request->expenseEntries), // store as JSON
            ]);

            // Step 2: Save journeys
            if ($request->ticketEntries && is_array($request->ticketEntries)) {
                foreach ($request->ticketEntries as $journey) {
                    Journey::create([
                        'tada_id' => $tada->id,
                        'tickets_provided_by_company' => $journey['tickets_provided_by_company'],
                        'from' => $journey['from'],
                        'to' => $journey['to'],
                        'date_of_journey' => date('Y-m-d H:i:s', strtotime($journey['date_of_journey'])),
                        'mode_of_transport' => $journey['mode_of_transport'],
                        'ticket' => $journey['ticket'], // just a string path
                        'amount' => $journey['amount']
                    ]);
                }
            }

            // Step 3: Save hotel expenses
            if ($request->guestHouseEntries && is_array($request->guestHouseEntries)) {
                foreach ($request->guestHouseEntries as $expense) {
                    HotelExpense::create([
                        'tada_id' => $tada->id,
                        'guest_house_available' => $expense['guest_house_available'],
                        'certificate_by_district_incharge' => $expense['certificate_by_district_incharge'] ?? null,
                        'check_in_date' => $expense['check_in_date'],
                        'check_out_date' => $expense['check_out_date'],
                        'breakfast_included' => $expense['breakfast_included'] ?? null,
                        'hotel_bill' => $expense['hotel_bill'] ?? null,
                        'amount' => $expense['amount'] ?? null,
                        'dining_cost' => $expense['dining_cost'] ?? null,
                    ]);
                }
            }

            FacadesDB::commit();

            return response()->json(['message' => 'TADA record created successfully.'], 201);
        } catch (\Exception $e) {
            FacadesDB::rollback();
            return response()->json(['error' => 'Failed to save TADA: ' . $e->getMessage()], 500);
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
    public function show($userId)
    {
        try {
            // Get all TADA records for this user with related journeys and hotel expenses
            $tadas = Tada::with(['journey', 'hotelExpense'])
                        ->where('user_id', $userId)
                        ->get();

            // Decode miscellaneous JSON for each TADA record
            $tadas->transform(function ($tada) {
                $tada->miscellaneous = json_decode($tada->miscellaneous);
                return $tada;
            });

            return response()->json([
                'user_id' => $userId,
                'tadas' => $tadas
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve TADA: ' . $e->getMessage()], 500);
        }
    }


     public function showConveyance(string $id){
        try {
            $conveyance = Conveyance::where('user_id', $id)->get();
            $data = $conveyance->map(function ($conv) {
                $vehicles = Vehicle::where('id', $conv->vehicle_category)->get(); // Adjust 'category' if your column is different
                $conv->vehicles = $vehicles; // Add vehicles as a dynamic property
                return $conv;
            });
            if (!$conveyance) {
                return response()->json([
                    'status' => false,
                    'message' => 'Conveyance not found'
                ], 404);
            }
            return response()->json([
                'status' => true,
                'message' => 'Conveyance fetched successfully',
                'data' => $data
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch conveyance',
                'error' => $th->getMessage()
            ]);
        }
    }

    public function getUserCategoryVehicle()
    {
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

    public function getUserCategory(Int $id)
    {
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
    public function update(Request $request, string $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
