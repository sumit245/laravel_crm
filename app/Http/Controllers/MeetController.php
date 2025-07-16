<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Meet;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class MeetController extends Controller
{
    public function index()
    {
        $meets = Meet::latest()->get();
        $projects = Project::all();
        // $users = User::all()->groupBy('role'); // Assuming you have role-based categories
        $usersByRole = [
            'Admins'            => User::where('role', 0)->get(),
            'Site Engineers'    => User::where('role', 1)->get(),
            'Project Managers'  => User::where('role', 2)->get(),
            'Vendors'           => User::where('role', 3)->get(),
            'Coordinators'      => User::where('role', 4)->get(),
            // Add more roles as needed
        ];
        return view('review-meetings.index', compact('meets', 'usersByRole', 'projects'));
    }

    public function create()
    {
        $meets = Meet::latest()->get();
        $projects = Project::all();
        // $users = User::all()->groupBy('role'); // Assuming you have role-based categories
        $usersByRole = [
            'Admins'            => User::where('role', 0)->get(),
            'Site Engineers'    => User::where('role', 1)->get(),
            'Project Managers'  => User::where('role', 2)->get(),
            'Vendors'           => User::where('role', 3)->get(),
            'Coordinators'      => User::where('role', 4)->get(),
            // Add more roles as needed
        ];
        return view('review-meetings.create', compact('meets', 'usersByRole', 'projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'agenda' => 'nullable|string',
            'meet_link' => 'required|url',
            'platform' => 'required|string',
            'meet_date' => 'required|date',
            'meet_time_from' => 'required',
            'meet_time_to' => 'required',
            'type' => 'required|string',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
        ]);

        $meet = Meet::create([
            ...$validated,
            'user_ids' => json_encode($validated['user_ids']),
        ]);

        // Optional: Send WhatsApp notification here later

        return redirect()->route('review-meetings.index')->with('success', 'Meeting created successfully!');
    }

    public function show(Meet $meet)
    {
        return view('review-meetings.show', compact('meet'));
    }

    public function edit(Meet $meet)
    {
        $users = User::all();
        return view('review-meetings.edit', compact('meet', 'users'));
    }

    public function update(Request $request, Meet $meet)
    {
        // similar to store logic, just with $meet->update()
    }

    public function destroy(Meet $meet)
    {
        $meet->delete();
        return redirect()->route('review-meetings.index')->with('success', 'Meeting deleted');
    }
}
