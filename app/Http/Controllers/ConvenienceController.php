<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserCategory;
use App\Models\Vehicle;
use Illuminate\Http\Request;

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
        return view('billing.convenience');
    }

    public function tada()
    {
        return  view('billing.tada');
    }

    public function settings()
    {
        $vehicles = Vehicle::get();
        $users = User::where('role', '!=', 3)->get();
        $categories = UserCategory::get();

        return view('billing.settings', compact('vehicles', 'users', 'categories'));
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
