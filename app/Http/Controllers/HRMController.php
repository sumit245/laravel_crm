<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HRMController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function preview(Request $request)
{
    $data = $request->except(['file_field_name']); // replace with actual file input name
    \Log::info('Storing form data in session:', $data);

    session(['formData' => $data]);

    // If you need the file temporarily, you can store its path or name separately
    $file = $request->file('file_field_name'); // get the file
    if ($file) {
        $filename = $file->getClientOriginalName();
        session(['uploaded_filename' => $filename]); // or move and store the path
    }

    return view('hrm.preview', compact('data'));
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
