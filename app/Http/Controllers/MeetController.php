<?php

namespace App\Http\Controllers;

use App\Helpers\WhatsappHelper;
use App\Http\Controllers\Controller;
use App\Models\Meet;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        Log::info($request);
        $validated = $request->validate([
            'title' => 'required|string',
            'agenda' => 'nullable|string',
            'meet_link' => 'required|url',
            'platform' => 'required|string',
            'meet_date' => 'required|date',
            'meet_time_from' => 'required',
            'meet_time_to' => 'required',
            'type' => 'required|string',
            'users' => 'required|array|min:1',
            'users.*' => 'exists:users,id',
        ]);

        $meet = Meet::create([
            ...$validated,
            'meet_time' => $validated['meet_time_from'],
            'user_ids' => json_encode($validated['users']),
        ]);

        // ✅ Fetch users and send WhatsApp invite
        $users = User::whereIn('id', $validated['users'])->get(['firstName', 'lastName', 'contactNo']);

        foreach ($users as $user) {
            try {
                WhatsappHelper::sendMeetLink(
                    $user->contactNo,
                    $user->firstName . ' ' . $user->lastName,
                    [
                        'firstName' => $user->firstName,
                        'lastName' => $user->lastName,
                        'title' => $validated['title'],
                        'meet_date' => $validated['meet_date'],
                        'meet_time' => $validated['meet_time_from'] . " - " . $validated["meet_time_to"],
                        'platform' => $validated['platform'],
                        'meet_link' => $validated['meet_link'],
                        'agenda' => $validated['agenda'] ?? '',
                        'type' => $validated['type'],
                    ]
                );
            } catch (\Exception $e) {
                Log::error("Failed to send WhatsApp to {$user->contactNo}: " . $e->getMessage());
            }
        }

        // Optional: Send WhatsApp notification here later

        return redirect()->route('meets.index')->with('success', 'Meeting created successfully!');
    }

    public function show(Meet $meet)
    {
        return view('review-meetings.show', compact('meet'));
    }

    public function edit(Meet $meet)
    {
        $users = User::all();
        return view('meets.edit', compact('meet', 'users'));
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
