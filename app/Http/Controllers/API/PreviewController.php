<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Candidate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;

class PreviewController extends Controller
{
    public function applyNow()
    {
        return view('hrm.applyNow');
    }

    public function storeAndPreview(Request $request)
    {
        // Validate the form data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:15',
            'dob' => 'required|date',
            'gender' => 'required|string',
            'marital_status' => 'required|string',
            'nationality' => 'required|string|max:50',
            'language' => 'required|string|max:50',
            
            // Contact Information
            'perm_house_no' => 'required|string|max:50',
            'perm_street' => 'required|string|max:100',
            'perm_city' => 'required|string|max:50',
            'perm_state' => 'required|string|max:50',
            'perm_country' => 'required|string|max:50',
            'perm_zip' => 'required|string|max:10',
            
            'curr_house_no' => 'required|string|max:50',
            'curr_street' => 'required|string|max:100',
            'curr_city' => 'required|string|max:50',
            'curr_state' => 'required|string|max:50',
            'curr_country' => 'required|string|max:50',
            'curr_zip' => 'required|string|max:10',
            
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:15',
            
            // Education (optional as it's dynamic)
            'education' => 'nullable|array',
            
            // Employment Details
            'position_applied_for' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'date_of_joining' => 'required|date',
            'previous_employer' => 'required|string|max:255',
            'experience' => 'required|numeric',
            'notice_period' => 'required|string|max:50',
            
            // Documents
            'document_name' => 'nullable|array',
            'document_file' => 'nullable|array',
            'document_file.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            
            // Additional Information
            'disabilities' => 'required|string|in:Yes,No',
            'currently_employed' => 'required|string|in:Yes,No',
            'reason_for_leaving' => 'nullable|string|max:255',
            'other_info' => 'nullable|string',
            
            // Photo
            'passport_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            
            // Declaration
            'signature' => 'required|string|max:255',
            'date' => 'required|date',
            'agree_terms' => 'required|accepted',
        ]);

        // Create a data array without file objects
        $data = $validatedData;
        
        // Format addresses
        $data['permanent_address'] = "{$data['perm_house_no']}, {$data['perm_street']}, {$data['perm_city']}, {$data['perm_state']}, {$data['perm_country']} - {$data['perm_zip']}";
        $data['current_address'] = "{$data['curr_house_no']}, {$data['curr_street']}, {$data['curr_city']}, {$data['curr_state']}, {$data['curr_country']} - {$data['curr_zip']}";
        
        // Process passport photo - store the file and save the path
        if ($request->hasFile('passport_photo')) {
            $photoPath = $request->file('passport_photo')->store('photos', 'public');
            $data['photo'] = $photoPath;
        }
        
        // Remove the file object from data array to prevent serialization issues
        unset($data['passport_photo']);
        
        // Process documents - store the files and save the paths
        $documents = [];
        if ($request->hasFile('document_file')) {
            foreach ($request->file('document_file') as $index => $file) {
                if ($file) {
                    $path = $file->store('documents', 'public');
                    $name = $request->document_name[$index] ?? "Document " . ($index + 1);
                    $documents[$name] = $path;
                }
            }
            $data['documents'] = $documents;
        }
        
        // Remove the file objects from data array
        unset($data['document_file']);
        
        // Store all data in session
        Session::put('candidate_data', $data);
        
        return redirect()->route('hrm.preview');
    }

    public function preview()
    {
        // Get data from session
        $data = Session::get('candidate_data');
        
        // If no data in session, redirect back to form
        if (!$data) {
            return redirect()->route('hrm.apply')->with('error', 'No form data found. Please fill out the form first.');
        }
        
        return view('hrm.preview', compact('data'));
    }

    public function submitFinal(Request $request)
    {
        // Get data from session
        $data = Session::get('candidate_data');
        
        // If no data in session, redirect back to form
        if (!$data) {
            return redirect()->route('hrm.apply')->with('error', 'No form data found. Please fill out the form first.');
        }
        
        try {
            // Create new candidate record
            $candidate = new Candidate();
            $candidate->name = $data['name'];
            $candidate->email = $data['email'];
            $candidate->phone = $data['phone'];
            $candidate->date_of_offer = now()->format('Y-m-d'); // Current date as offer date
            $candidate->address = $data['current_address'];
            $candidate->designation = $data['position_applied_for'];
            $candidate->department = $data['department'];
            $candidate->location = $data['curr_city'] . ', ' . $data['curr_state'];
            $candidate->doj = $data['date_of_joining'];
            $candidate->experience = $data['experience'];
            
            // Additional fields from the form
            $candidate->gender = $data['gender'] ?? null;
            $candidate->marital_status = $data['marital_status'] ?? null;
            $candidate->nationality = $data['nationality'] ?? null;
            $candidate->language = $data['language'] ?? null;
            $candidate->permanent_address = $data['permanent_address'] ?? null;
            $candidate->emergency_contact_name = $data['emergency_contact_name'] ?? null;
            $candidate->emergency_contact_phone = $data['emergency_contact_phone'] ?? null;
            $candidate->previous_employer = $data['previous_employer'] ?? null;
            $candidate->notice_period = $data['notice_period'] ?? null;
            $candidate->disabilities = $data['disabilities'] ?? null;
            $candidate->currently_employed = $data['currently_employed'] ?? null;
            $candidate->reason_for_leaving = $data['reason_for_leaving'] ?? null;
            $candidate->other_info = $data['other_info'] ?? null;
            $candidate->photo = $data['photo'] ?? null;
            $candidate->signature = $data['signature'] ?? null;
            
            // Store education as JSON
            if (isset($data['education'])) {
                $candidate->education = json_encode($data['education']);
            }
            
            // Store document paths as JSON
            if (isset($data['documents'])) {
                $candidate->document_path = json_encode($data['documents']);
            }
            
            // Set status to pending by default
            $candidate->status = 'pending';
            
            // Save the candidate
            $candidate->save();
            
            // Generate a reference number
            $referenceNumber = 'SL-' . date('Ymd') . '-' . $candidate->id;
            
            // Clear the session data
            Session::forget('candidate_data');
            
            return redirect()->route('hrm.success')->with('success', 'Your application has been submitted successfully!')->with('reference_number', $referenceNumber);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error submitting application: ' . $e->getMessage());
        }
    }
}