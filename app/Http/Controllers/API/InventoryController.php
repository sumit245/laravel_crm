<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Imports\InventoryImport;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class InventoryController extends Controller
{
 /**
  * Display a listing of the resource.
  */
 public function index()
 {
  return Inventory::with(['project', 'site'])->get();
 }

 /**
  * Store a newly created resource in storage.
  */
 public function store(Request $request)
 {
  try {
   $validated = $request->validate([
    'productName'     => 'required|string',
    'brand'           => 'required|string',
    'description'     => 'nullable|string',
    'unit'            => 'required|string',
    'initialQuantity' => 'string',
    'quantityStock'   => 'string',
   ]);
   $inventory = Inventory::create($validated);
   return response()->json([
    'message' => 'Inventory created successfully',
    'data'    => $inventory,
   ]);
  } catch (\Exception $e) {
   return response()->json(['message' => $e->getMessage()], 500);
  }
 }

 /**
  * Display the specified resource.
  */
 public function show($id)
 {
  return Inventory::with(['project', 'site', 'task'])->findOrFail($id);
 }

 /**
  * Update the specified resource in storage.
  */
 public function update(Request $request, $id)
 {
  $inventory = Inventory::findOrFail($id);
  $inventory->update($request->all());
  return $inventory;
 }
 public function import(Request $request)
 {
  $request->validate([
   'file' => 'required|mimes:xlsx,xls,csv|max:2048',
  ]);

  try {
   Excel::import(new InventoryImport, $request->file('file'));
   return redirect()->route('inventory.index')->with('success', 'Inventory imported successfully!');
  } catch (\Exception $e) {
   return redirect()->back()->withErrors(['error' => $e->getMessage()]);
  }
 }
 /**
  * Remove the specified resource from storage.
  */
 public function destroy($id)
 {
  $inventory = Inventory::findOrFail($id);
  $inventory->delete();
  return response()->json(['message' => 'Inventory deleted']);
 }

}
