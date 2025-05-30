<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Candidate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;



class PreviewController extends Controller
{
    public function applyNow()
    {
        return view('hrm.applyNow');
    }

    public function storeAndPreview(Request $request)
    {
        // Validate the form data
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|email',
            'phone' => 'required|string|max:15',
            'dob' => 'required|date',
            'gender' => 'required|string',
            'marital_status' => 'required|string',
            'nationality' => 'required|string|max:50',
            'language' => 'required|string',
            // Contact Information
            'perm_house_no' => 'required|string',
            'perm_street' => 'required|string',
            'perm_city' => 'required|string|max:50',
            'perm_state' => 'required|string|max:50',
            'perm_country' => 'required|string|max:50',
            'perm_zip' => 'required|string|max:10',
            'curr_house_no' => 'required|string',
            'curr_street' => 'required|string',
            'curr_city' => 'required|string|max:50',
            'curr_state' => 'required|string|max:50',
            'curr_country' => 'required|string|max:50',
            'curr_zip' => 'required|string|max:10',
            'emergency_contact_name' => 'required|string|max:50',
            'emergency_contact_phone' => 'required|string|max:15',
            // Education (array)
            'education' => 'required|array',
            // Employment Details
            'position_applied_for' => 'required|string',
            'department' => 'required|string',
            'date_of_joining' => 'required|date',
            'previous_employer' => 'required|string',
            'experience' => 'required|numeric|min:0|max:50',
            'notice_period' => 'required|string',
            // Additional Information
            'disabilities' => 'required|string',
            'currently_employed' => 'required|string',
            'reason_for_leaving' => 'nullable|string',
            'other_info' => 'nullable|string',
            // Declaration
            'signature' => 'required|string',
            'date' => 'required|date',
            'agree_terms' => 'required',
        ]);

        // Format addresses for display
        $permanentAddress = $request->perm_house_no . ', ' . 
                           $request->perm_street . ', ' . 
                           $request->perm_city . ', ' . 
                           $request->perm_state . ', ' . 
                           $request->perm_country . ' - ' . 
                           $request->perm_zip;
        
        $currentAddress = $request->curr_house_no . ', ' . 
                         $request->curr_street . ', ' . 
                         $request->curr_city . ', ' . 
                         $request->curr_state . ', ' . 
                         $request->curr_country . ' - ' . 
                         $request->curr_zip;

        // Handle file uploads
        $documents = [];
        $documentPaths = [];
        if ($request->hasFile('document_file')) {
            foreach ($request->file('document_file') as $index => $file) {
                if ($file->isValid()) {
                    $docName = $request->document_name[$index] ?? 'Document ' . ($index + 1);
                    $path = $file->store('documents', 'public');
                    $documents[$docName] = $path;
                    $documentPaths[] = $path;
                }
            }
        }

        // Handle passport photo upload
        $photoPath = null;
        if ($request->hasFile('passport_photo') && $request->file('passport_photo')->isValid()) {
            $photoPath = $request->file('passport_photo')->store('photos', 'public');
        }

        // Store all data in session
        $formData = [
            // Personal Information
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'dob' => $request->dob,
            'gender' => $request->gender,
            'marital_status' => $request->marital_status,
            'nationality' => $request->nationality,
            'language' => $request->language,
            
            // Contact Information
            'permanent_address' => $permanentAddress,
            'current_address' => $currentAddress,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            
            // Education
            'education' => $request->education,
            
            // Employment Details
            'position_applied_for' => $request->position_applied_for,
            'department' => $request->department,
            'date_of_joining' => $request->date_of_joining,
            'previous_employer' => $request->previous_employer,
            'experience' => $request->experience,
            'notice_period' => $request->notice_period,
            
            // Additional Information
            'disabilities' => $request->disabilities,
            'currently_employed' => $request->currently_employed,
            'reason_for_leaving' => $request->reason_for_leaving,
            'other_info' => $request->other_info,
            
            // Documents
            'documents' => $documents,
            'document_paths' => $documentPaths,
            'photo' => $photoPath,
            
            // Declaration
            'signature' => $request->signature,
            'date' => $request->date,
            
            // Raw data for form fields
            // ✅ Safely exclude files
'raw_data' => $request->except(['document_file', 'passport_photo'])

        ];

        // Store in session
        session(['employee_form_data' => $formData]);

        // Return the preview view with the data
        return view('hrm.preview', ['data' => $formData]);
    }

    public function preview()
    {
        // Get data from session
        $data = session('employee_form_data', []);
        
        // If no data in session, redirect back to form
        if (empty($data)) {
            return redirect()->route('hrm.apply')->with('error', 'No form data found. Please fill the form first.');
        }
        
        return view('hrm.preview', ['data' => $data]);
    }

    public function submitFinal(Request $request)
    {
        // Get data from session
        $data = session('employee_form_data', []);
        
        // If no data in session, redirect back to form
        if (empty($data)) {
            return redirect()->route('hrm.apply')->with('error', 'No form data found. Please fill the form first.');
        }
        
        // Prepare data for Candidate model
        $candidateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'date_of_offer' => now()->format('Y-m-d'), // Current date as offer date
            'address' => $data['current_address'],
            'designation' => $data['position_applied_for'],
            'department' => $data['department'],
            'location' => explode(',', $data['current_address'])[2] ?? '', // Extract city from address
            'doj' => $data['date_of_joining'],
            'ctc' => 0, // Default value, update as needed
            'experience' => $data['experience'],
            'last_salary' => 0, // Default value, update as needed
            'document_path' => json_encode($data['document_paths'] ?? []),
            'status' => 'pending'
        ];
        
        // Create the candidate record
        $candidate = Candidate::create($candidateData);
        
        // Clear the session data
        session()->forget('employee_form_data');
        
        // Redirect to success page
        return redirect()->route('hrm.success')->with('success', 'Your application has been submitted successfully!');
    }
}