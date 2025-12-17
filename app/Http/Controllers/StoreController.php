<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Stores;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StoreController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $storeModel = Stores::all();
        //   return view('tasks.index', compact('tasks'));
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
    public function store(Request $request, $projectId)
    {
        try {
            $validated = $request->validate([
                'name'          => 'required|string|max:255',
                'address'       => 'required|string|max:500',
                'storeIncharge' => 'required|exists:users,id',
            ]);

            Stores::create([
                'project_id'        => $projectId,
                'store_name'        => $validated['name'],
                'address'           => $validated['address'],
                'store_incharge_id' => $validated['storeIncharge'],
            ]);

            $project = Project::with('stores')->findOrFail($projectId);
            $users   = User::where('role', '!=', \App\Enums\UserRole::VENDOR->value)->get();
            // TODO: Also remove role 1
            // Redirect back to the project detail page with updated data
            return redirect()->back()->with('success', 'Store Created Successfully');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to create store. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $store = Stores::with('user')->findOrFail($id);
        return view('stores.show', compact('store'));
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
    public function destroy($id)
    {
        try {
            $store = Stores::findOrFail($id);
            $store->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
