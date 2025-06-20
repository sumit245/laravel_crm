<?php

namespace App\Http\Controllers;

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
use Illuminate\Http\Request;
use Log;

class ConvenienceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function convenience()
    {
        //
        $cons = Conveyance::get();
        $appliedAmount = Conveyance::sum('amount');
        $disbursedAmount = Conveyance::where('status', 1)->sum('amount');
        $rejectedAmount = Conveyance::where('status', 0)->sum('amount');


        return view('billing.convenience', compact('cons', 'appliedAmount', 'disbursedAmount', 'rejectedAmount'));
    }

    public function showdetailsconveyance($id)
    {

        $details = Conveyance::with(['user', 'vehicle'])->where('user_id', $id)->get();
        $appliedAmount = Conveyance::where('user_id', $id)->sum('amount');
        $disbursedAmount = Conveyance::where('user_id', $id)->where('status', 1)->sum('amount');
        $rejectedAmount = Conveyance::where('user_id', $id)->where('status', 0)->sum('amount');
        $dueclaimAmount = $appliedAmount - $disbursedAmount;
        // dd($details);
        return view('billing.conveyanceDetails', compact('details', 'appliedAmount', 'disbursedAmount', 'rejectedAmount', 'dueclaimAmount'));
    }

    public function accept($id)
    {
        Conveyance::where('id', $id)->update(['status' => 1]);
        return back()->with('success', 'Status updated to Accepted.');
    }

    public function reject($id)
    {
        Conveyance::where('id', $id)->update(['status' => 0]);

        return back()->with('success', 'Status updated to Accepted.');
    }

    // Tada view
    public function tadaView()
    {
        $tadas = Tada::with(['user', 'journey', 'hotelExpense'])->get();
        $pendingclaimcount = Tada::whereNull('status')->count();
        $acceptedClaim = Tada::where('status', 1)->count();
        $rejectedClaim = Tada::where('status', 0)->count();
        $trips = $tadas->count();

        $tadasWithTotals = [];
        $grandTotalAmount = 0; // New variable to track total for all users

        foreach ($tadas as $tada) {
            // Sum Journey amounts
            $journeyTotal = $tada->journey->sum('amount');

            // Sum Hotel amounts and other charges
            $hotelTotal = $tada->hotelExpense->sum('amount');
            $otherChargesTotal = $tada->hotelExpense->sum('other_charges');

            // Sum Miscellaneous (JSON field)
            $miscTotal = 0;
            if (!empty($tada->miscellaneous)) {
                $miscItems = json_decode($tada->miscellaneous, true);
                if (is_array($miscItems)) {
                    foreach ($miscItems as $item) {
                        $miscTotal += isset($item['amount']) ? $item['amount'] : 0;
                    }
                }
            }

            $totalAmount = $journeyTotal + $hotelTotal + $otherChargesTotal + $miscTotal;

            // Accumulate grand total
            $grandTotalAmount += $totalAmount;

            // Append total to each TADA
            $tadasWithTotals[] = [
                'tada' => $tada,
                'total_amount' => $totalAmount
            ];
        }

        return view('billing.tada', compact('tadasWithTotals', 'pendingclaimcount', 'trips', 'grandTotalAmount', 'rejectedClaim', 'acceptedClaim'));
    }



    public function viewtadaDetails(String $id)
    {
        $tadas = Tada::with('journey', 'hotelExpense', 'user')->where('id', $id)->first();
        $travelfares = Journey::where('tada_id', $tadas->id)->get();
        $dailyfares = HotelExpense::where('tada_id', $tadas->id)->get();
        $miscData = json_decode($tadas->miscellaneous, true);
        $otherExpense = collect($miscData)->sum('amount');
        $travelfare = collect($travelfares)->sum('amount');
        $hotelfare = collect($dailyfares)->sum('amount');
        $hotelOtherCharges = collect($dailyfares)->sum('other_charges');
        $hotelExpense = $hotelfare + $hotelOtherCharges;
        $conveyance = $travelfare + $hotelExpense;
        $totalamount = $conveyance + $otherExpense;
        return view('billing.tadaDetails', compact('tadas', 'travelfares', 'dailyfares', 'hotelExpense', 'travelfare', 'otherExpense', 'totalamount'));
    }

    // public function updateTadaStatus(Request $request, $id)
    // {
    //     try {
    //         // Validate request
    //         $validated = $request->validate([
    //             'status' => 'required|boolean',
    //         ]);

    //         // Find the TADA record
    //         $tada = Tada::findOrFail($id);

    //         // Update the status
    //         $tada->status = $validated['status'];
    //         $tada->update();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'TADA status updated successfully',
    //             'data' => $tada
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Failed to update TADA status: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function bulkUpdateStatus(Request $request)
{
    $validated = $request->validate([
        'ids' => 'required|array',
        'status' => 'required|in:0,1'
    ]);

    Tada::whereIn('id', $validated['ids'])->update(['status' => $validated['status']]);

    return response()->json(['message' => 'Status updated successfully.']);
}


    public function settings()
    {
        $vehicles = Vehicle::get();
        $users = User::with('usercategory')->where('role', '!=', 3)->get();

        $categories = UserCategory::get();
        $vehicleNames = $vehicles->pluck('name', 'id')->toArray();

        $cities = City::with('usercategory')->get();

        return view('billing.settings', compact('vehicles', 'users', 'categories', 'vehicleNames', 'cities'));
    }

    // Add vehicle function
    public function addVehicle(Request $request)
    {
        // Check if category already exists
        $existingCategory = Vehicle::where('category', $request->category)->first();

        if ($existingCategory) {
            return redirect()->back()->with('error', 'This category already exists. Please use a different category.');
        }

        // Validate the input
        $validatedData = $request->validate([
            'vehicle_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'sub_category' => 'nullable|string|max:255',
            'rate' => 'required|numeric',
        ]);

        // Save the vehicle
        Vehicle::create($validatedData);

        return redirect()->back()->with('success', 'Vehicle added successfully!');
    }

    public function editVehicle(Request $request)
    {
        $ev = Vehicle::find($request->id);
        return view('billing.editVehicle', compact('ev'));
    }

    public function updateVehicle(Request $request)
    {

        $id = $request->input('user_id'); // Vehicle ID from the hidden input field

        // Validate the required fields: 'category' and 'rate'
        $validatedData = $request->validate([
            'vehicle_name' => 'nullable|string|max:100',  // Optional field
            'subcategory' => 'nullable|string|max:50',  // Optional field
            'rate' => 'required|numeric',  // Rate is required and must be numeric
        ]);

        try {
            // Find the vehicle by its ID
            $vehicle = Vehicle::findOrFail($id);

            // Update the fields using the update method
            $vehicle->update($validatedData);

            // Redirect to the settings page with a success message
            return redirect()->route('billing.settings')->with('success', 'Vehicle updated successfully!');
        } catch (\Exception $e) {
            // Return to the same page with an error message
            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the vehicle. Please try again.']);
        }
    }

    public function deleteVehicle(Request $request)
    {
        $dv = Vehicle::find($request->id);
        $dv->delete();
        return redirect()->back()->with('success', 'Vehicle deleted successfully!');
    }



    // User Edit
    public function editUser(Request $request)
    {
        $ue = User::find($request->id);
        $uc = UserCategory::select('category_code')->distinct()->get();

        return view('billing.editUser', compact('ue', 'uc'));
    }


    public function updateUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'category' => 'required|integer|exists:user_categories,id', // ID of user_category
        ]);

        $user = User::findOrFail($request->input('user_id'));

        $userCategory = UserCategory::findOrFail($request->input('category'));

        $user->category_code = $userCategory->category_code;
        $user->save();

        return redirect()->route('billing.settings')->with('success', 'User category updated successfully!');
    }

    public function viewCategory()
    {
        $vehicles = Vehicle::get();
        return view('billing.addCategory', compact('vehicles'));
    }
    // Add Category
    public function addCategory(Request $request)
    {
        $validatedData = $request->validate([
            'category' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'vehicle_id' => 'required|array',
            'vehicle_id.*' => 'exists:vehicles,id',
            'city_category' => 'required|numeric',
            'daily_amount' => 'required|numeric'
        ]);

        // Custom check: ensure category + city combination is unique
        $existing = UserCategory::where('category_code', $validatedData['category'])
            ->where('city_category', $validatedData['city_category'])
            ->exists();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['category' => 'User category and city category already exists.']);
        }

        try {
            // Save the new category
            $category = new UserCategory();
            $category->category_code = $validatedData['category'];
            $category->name = $validatedData['name'];
            $category->description = $validatedData['description'] ?? null;
            $category->allowed_vehicles = json_encode($validatedData['vehicle_id']);
            $category->city_category = $validatedData['city_category'];
            $category->dailyamount = $validatedData['daily_amount'];
            $category->save();

            return redirect()->route('billing.settings', ['tab' => 'category'])->with('success', 'Category added successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'An unexpected error occurred while saving the category. Please try again.']);
        }
    }



    public function editCategory(Request $request)
    {
        $uc = UserCategory::find($request->id);
        $uv = Vehicle::get();
        return view('billing.editCategory', compact('uc', 'uv'));
    }

    public function updateCategory(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'daily_amount' => 'required|numeric',
                'vehicle_id' => 'required|array',
                'vehicle_id.*' => 'exists:vehicles,id',
            ]);
            $category = UserCategory::find($request->category_id);
            if (!$category) {
                return redirect()->back()->with('error', 'Category not found.');
            }

            $category->dailyamount = $validatedData['daily_amount'];
            $category->allowed_vehicles = json_encode($validatedData['vehicle_id']); // Store as JSON if needed
            $category->save();

            return redirect()->route('billing.settings')->with('success', 'Category updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while updating the category.');
        }
    }


    // Delete Category
    public function deleteCategory(Request $request)
    {
        $dc = UserCategory::find($request->id);
        $dc->delete();
        return redirect()->back()->with('success', 'Category deleted successfully!');
    }

    public function editAllowedExpense($id){
        $city = City::with('usercategory')->findOrFail($id);
        $usercategory = UserCategory::where('id', $city->user_category_id)->first();
        $usercategories = UserCategory::get();
        return view('billing.editAllowedExpense', compact('city', 'usercategory', 'usercategories'));
    }

    public function updateAllowedExpense(Request $request, $id)
    {
        // Validate the request data
        $validated = $request->validate([
            'city_name' => 'string',
            'city_category' => 'required|in:0,1,2',
        ]);

        // Find the city
        $city = City::findOrFail($id);

        // Update city model fields
        $city->category = $validated['city_category'];
        $city->name = $validated['city_name'];

        // Save the city
        $city->save();

        // Redirect back with success message
        return redirect()->route('billing.settings')
                        ->with('success', 'Allowed expense updated successfully.');
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
        //
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
