<?php

namespace App\Http\Controllers;

use App\Helpers\WhatsappHelper;
use App\Http\Controllers\Controller;
use App\Models\Meet;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MeetController extends Controller
{
    public function index()
    {
        $meets = Meet::latest()->get();
        $projects = Project::all();
        // $users = User::all()->groupBy('role'); // Assuming you have role-based categories
        $usersByRole = [
            'Admins' => User::where('role', 0)->get(),
            'Site Engineers' => User::where('role', 1)->get(),
            'Project Managers' => User::where('role', 2)->get(),
            'Vendors' => User::where('role', 3)->get(),
            'Coordinators' => User::where('role', 4)->get(),
            // Add more roles as needed
        ];
        return view('review-meetings.index', compact('meets', 'usersByRole', 'projects'));
    }

    public function create()
    {
        $meets = Meet::latest()->get();
        $projects = Project::all();

        // Return all users except role 3 (Vendors)
        $users = User::where('role', '<>', 3)->get();

        return view('review-meetings.create', compact('meets', 'users', 'projects'));
    }

    public function store(Request $request)
    {
        Log::info($request->all());

        // 1) Validate meeting basic fields (do not validate users.* yet)
        $validatedBase = $request->validate([
            'title' => 'required|string',
            'agenda' => 'nullable|string',
            'meet_link' => 'required|url',
            'platform' => 'required|string',
            'meet_date' => 'required|date',
            'meet_time_from' => 'required',
            'meet_time_to' => 'required',
            'type' => 'required|string',
        ]);

        $createdUserIds = [];

        // 2) Handle "new_participants" from the form (array of objects)
        $newParticipants = $request->input('new_participants', []);
        if (!empty($newParticipants) && is_array($newParticipants)) {
            foreach ($newParticipants as $idx => $np) {
                // Basic validation per participant (skip empty rows)
                $np = array_map('trim', (array) $np);
                if (empty($np['firstName']) && empty($np['lastName']) && empty($np['email']) && empty($np['contactNo'])) {
                    continue; // skip entirely empty row
                }

                $v = Validator::make($np, [
                    'firstName' => 'required|string|max:255',
                    'lastName' => 'nullable|string|max:255',
                    'email' => 'nullable|email|max:255',
                    'contactNo' => 'nullable|string|max:50',
                ]);

                if ($v->fails()) {
                    return back()->withErrors($v)->withInput();
                }

                // Try to find existing user by email or contactNo; create if not found
                $existingQuery = User::query();
                if (!empty($np['email'])) {
                    $existingQuery->where('email', $np['email']);
                }
                if (!empty($np['contactNo'])) {
                    $existingQuery->orWhere('contactNo', $np['contactNo'])->orWhere('contactNo', $np['contactNo']);
                }
                $existing = $existingQuery->first();

                if ($existing) {
                    $createdUserIds[] = $existing->id;
                    continue;
                }

                // Create a minimal user. Adjust role as needed (using 4 as generic participant).
                $password = Str::random(12);
                $user = User::create([
                    'firstName' => $np['firstName'] ?? null,
                    'lastName' => $np['lastName'] ?? null,
                    'email' => $np['email'] ?? null,
                    'contactNo' => $np['contactNo'] ?? null,
                    'role' => 100,
                    'password' => bcrypt($password),
                ]);

                $createdUserIds[] = $user->id;
            }
        }

        // 3) Handle CSV import (optional file input name = import_participants)
        if ($request->hasFile('import_participants')) {
            $file = $request->file('import_participants');
            if ($file->isValid()) {
                if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                    $header = null;
                    while (($row = fgetcsv($handle, 0, ',')) !== false) {
                        if (!$header) {
                            // assume header exists, normalize
                            $header = array_map('trim', $row);
                            continue;
                        }
                        $data = array_combine($header, $row);
                        $first = trim($data['firstName'] ?? $data['firstName'] ?? ($data['first'] ?? ''));
                        $last = trim($data['lastName'] ?? $data['lastName'] ?? ($data['last'] ?? ''));
                        $email = trim($data['email'] ?? '');
                        $phone = trim($data['contactNo'] ?? $data['phone'] ?? '');

                        if (empty($first) && empty($email) && empty($phone)) {
                            continue;
                        }

                        $existing = User::where(function ($q) use ($email, $phone) {
                            if ($email)
                                $q->orWhere('email', $email);
                            if ($phone)
                                $q->orWhere('contactNo', $phone)->orWhere('contactNo', $phone);
                        })->first();

                        if ($existing) {
                            $createdUserIds[] = $existing->id;
                            continue;
                        }

                        $user = User::create([
                            'firstName' => $first ?: null,
                            'lastName' => $last ?: null,
                            'email' => $email ?: null,
                            'contactNo' => $phone ?: null,
                            'role' => 100,
                            'password' => bcrypt(Str::random(12)),
                        ]);
                        $createdUserIds[] = $user->id;
                    }
                    fclose($handle);
                }
            }
        }

        // 4) Combine selected checkboxes with created users
        $selectedUsers = $request->input('users', []);
        if (!is_array($selectedUsers)) {
            $selectedUsers = [];
        }
        $allUserIds = array_values(array_unique(array_merge($selectedUsers, $createdUserIds)));

        // 5) Now validate users array (ensures they exist)
        $request->merge(['users' => $allUserIds]);
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

        // 6) Create meet (store user_ids JSON)
        $meet = Meet::create([
            ...$validated,
            'meet_time' => $validated['meet_time_from'],
            'user_ids' => json_encode($validated['users']),
        ]);

        // 7) Send WhatsApp invites
        $users = User::whereIn('id', $validated['users'])->get(['firstName', 'lastName', 'contactNo']);
        foreach ($users as $user) {
            try {
                WhatsappHelper::sendMeetLink($user->contactNo, $user->firstName . ' ' . $user->lastName, [
                    'firstName' => $user->firstName,
                    'lastName' => $user->lastName,
                    'title' => $validated['title'],
                    'meet_date' => $validated['meet_date'],
                    'meet_time' => $validated['meet_time_from'] . ' - ' . $validated['meet_time_to'],
                    'platform' => $validated['platform'],
                    'meet_link' => $validated['meet_link'],
                    'agenda' => $validated['agenda'] ?? '',
                    'type' => $validated['type'],
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to send WhatsApp to {$user->contactNo}: " . $e->getMessage());
            }
        }

        return redirect()->route('meets.index')->with('success', 'Meeting created successfully!');
    }
    public function show(Meet $meet)
    {
        // Load the relationship
        $historicalNotes = $meet->notesHistory()->with('user')->get();

        return view('review-meetings.notes', [
            'meet' => $meet,
            'historicalNotes' => $historicalNotes,
        ]);
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

    public function notes(Meet $meet)
    {
        // Show editor + whiteboard page
        return view('review-meetings.notes', compact('meet'));
    }

    public function updateNotes(Request $request, Meet $meet)
    {
        $data = $request->validate([
            'notes' => 'nullable|string',
            'whiteboard_dataurl' => 'nullable|string', // data:image/png;base64,....
            'insert_whiteboard_into_notes' => 'nullable|boolean',
        ]);

        // Save drawing if provided
        if (!empty($data['whiteboard_dataurl']) && str_starts_with($data['whiteboard_dataurl'], 'data:image/png;base64,')) {
            $png = substr($data['whiteboard_dataurl'], strpos($data['whiteboard_dataurl'], ',') + 1);
            $png = base64_decode(str_replace(' ', '+', $png));
            $path = 'whiteboards/meet-' . $meet->id . '-' . time() . '.png';
            Storage::disk('public')->put($path, $png);
            $meet->whiteboard_image_path = $path;

            // optionally insert the drawing <img> into notes content
            if ($request->boolean('insert_whiteboard_into_notes')) {
                $imgTag = '<p><img src="' . asset('storage/' . $path) . '" alt="Whiteboard" style="max-width:100%"></p>';
                $data['notes'] = ($data['notes'] ?? '') . $imgTag;
            }
        }

        if (array_key_exists('notes', $data)) {
            $meet->notes = $data['notes'];
        }

        $meet->save();

        return back()->with('success', 'Notes and whiteboard saved.');
    }

    public function exportPdf(Meet $meet)
    {
        // Embed whiteboard as base64 for dompdf (reliable)
        $whiteboardBase64 = null;
        if ($meet->whiteboard_image_path && Storage::disk('public')->exists($meet->whiteboard_image_path)) {
            $bytes = Storage::disk('public')->get($meet->whiteboard_image_path);
            $whiteboardBase64 = 'data:image/png;base64,' . base64_encode($bytes);
        }

        $pdf = Pdf::loadView('meets.pdf', [
            'meet' => $meet,
            'whiteboardBase64' => $whiteboardBase64,
        ])->setPaper('a4');

        return $pdf->download('meeting_' . $meet->id . '.pdf');
    }
    // OPTIONAL Excel export (simple single-sheet)
    public function exportExcel(Meet $meet)
    {
        return Excel::download(new \App\Exports\MeetingNotesExport($meet), 'meeting_' . $meet->id . '.xlsx');
    }
}
