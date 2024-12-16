<?php

namespace App\Http\Controllers;

use App\Imports\InventoryImport;
use App\Models\Inventory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class InventoryController extends Controller
{
 /**
  * Display a listing of the resource.
  */
 public function index()
 {
  //
  $inventory = Inventory::all();
  return view('inventory.index', compact('inventory'));
 }

 public function import(Request $request)
 {
  $request->validate([
   'file' => 'required|mimes:xlsx,xls,csv|max:2048',
  ]);
  $projectId = $request->projectId;
  $storeId   = $request->storeId;
  Log::info($storeId);
  try {
   Excel::import(new InventoryImport($projectId, $storeId), $request->file('file'));
   return redirect()->route('inventory.index')->with('success', 'Inventory imported successfully!');
  } catch (\Exception $e) {
//    return alert('Error importing inventory: ' . $e->getMessage());
   return redirect()->back()->withErrors(['error' => $e->getMessage()]);
  }
 }

 /**
  * Show the form for creating a new resource.
  */
 public function create()
 {
  //
  return view('inventory.create');
 }

 /**
  * Store a newly created resource in storage.
  */
 public function store(Request $request)
 {
  // Validate the incoming data without requiring a username
  $validated = $request->validate([
   'productName'     => 'required|string|max:255',
   'brand'           => 'nullable|string',
   'description'     => 'nullable|string',
   'initialQuantity' => 'required|string',
   'quantityStock'   => 'nullable|string',
   'unit'            => 'required|string|max:25',
   'receivedDate'    => 'nullable|date',
  ]);

  try {

   $inventory = Inventory::create($validated);

   return redirect()->route('inventory.show', $inventory->id)
    ->with('success', 'Inventory created successfully.');
  } catch (\Exception $e) {
   // Catch database or other errors
   $errorMessage = $e->getMessage();

   return redirect()->back()
    ->withErrors(['error' => $errorMessage])
    ->withInput();
  }
 }

 /**
  * Display the specified resource.
  */
 public function show(string $id)
 {
  //
  $item = Inventory::findOrFail($id);
  return view('inventory.show', compact('item'));
 }

 /**
  * Show the form for editing the specified resource.
  */
 public function edit(string $id)
 {
  //
  $item = Inventory::findOrFail($id);
  return view('inventory.edit', compact('item'));
 }

 /**
  * Update the specified resource in storage.
  */
 public function update(Request $request, Inventory $item)
 {
  //
  // Validate the incoming data without requiring a username
  $validated = $request->validate([
   'productName'     => 'required|string|max:255',
   'brand'           => 'nullable|string',
   'description'     => 'nullable|string',
   'initialQuantity' => 'required|string',
   'quantityStock'   => 'nullable|string',
   'unit'            => 'required|string|max:25',
   'receivedDate'    => 'nullable|date',
  ]);

  try {

   $item->update($validated);
   return redirect()->route('inventory.show', compact('item'))
    ->with('success', 'Inventory updated successfully.');
  } catch (\Exception $e) {
   // Catch database or other errors
   $errorMessage = $e->getMessage();

   return redirect()->back()
    ->withErrors(['error' => $errorMessage])
    ->withInput();
  }
 }

 /**
  * Remove the specified resource from storage.
  */
 public function destroy(string $id)
 {
  //
  try {
   Inventory::findOrFail($id)->delete();
   return response()->json(['success' => true, 'message' => 'Item deleted successfully.']);
  } catch (\Exception $e) {
   return response()->json(['success' => false, 'message' => 'Failed to delete Item'], 500);
  }
 }

 public function viewInventory(Request $request)
 {

  try {
   $projectId = $request->project_id;
   $storeName = $request->store_name;
   $inventory = Inventory::whereHas('store', function ($query) use ($storeName) {
    $query->where('store_name', $storeName);
   })
    ->where('project_id', $projectId)
    ->get();

   return view('inventory.view', compact('inventory', 'projectId', 'storeName'));

  } catch (Exception $e) {
   Log::info($e->getMessage());
  }
 }

}
