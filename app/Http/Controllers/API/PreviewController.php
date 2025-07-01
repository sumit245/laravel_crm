<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Candidate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Log;
use PHPUnit\Event\Code\Throwable;



class PreviewController extends Controller
{
    public function applyNow()
    {
        return view('hrm.applyNow');
    }

    public function storeAndPreview(Request $request)
    {
        // Log::info($request->all());
        // Validate the form data
        try{
            $validated = $request->validate([
            'id' => 'required',
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
            'employment' => 'required|array',
            'position_applied_for' => 'required|string',
            'department' => 'required|string',
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
            'id' =>$request->id,
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
            'experience' => $request->experience,
            'notice_period' => $request->notice_period,
            'employment' => $request->employment,
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
            // Safely exclude files
            'raw_data' => $request->except(['document_file', 'passport_photo'])

        ];

        // Store in session
        session(['employee_form_data' => $formData]);

        // Return the preview view with the data
        return view('hrm.preview', ['data' => $formData]);
        }
        catch (\Throwable $th) {
            Log::error('Error storing preview form: ' . $th->getMessage());
            return back()->with('error', 'An error occurred while processing your application. Please review the form and try again.');
    }
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
        try {
            // Get data from session
        $data = session('employee_form_data', []);
        Log::info($data);
        $id = $data['id'];

        // If no data in session, redirect back to form
        if (empty($data)) {
            return redirect()->route('hrm.apply')->with('error', 'No form data found. Please fill the form first.');
        }

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $photoFile = $request->file('photo');
            $photoFilename = time() . '_photo_' . uniqid() . '.' . $photoFile->getClientOriginalExtension();
            $photoPath = Storage::disk('s3')->putFileAs('candidate_photos', $photoFile, $photoFilename);
            $photoUrl = Storage::disk('s3')->url($photoPath);
            $data['photo'] = $photoUrl;
            $data['photo_name'] = $photoFile->getClientOriginalName();
            $data['photo_s3_path'] = $photoPath;
        }

        if ($request->hasFile('signature') && $request->file('signature')->isValid()) {
            $signatureFile = $request->file('signature');
            $signatureFilename = time() . '_signature_' . uniqid() . '.' . $signatureFile->getClientOriginalExtension();
            $signaturePath = Storage::disk('s3')->putFileAs('candidate_signatures', $signatureFile, $signatureFilename);
            $signatureUrl = Storage::disk('s3')->url($signaturePath);
            $data['signature'] = $signatureUrl;
        }

        $uploadedDocuments = [];
        if ($request->hasFile('document_paths')) {
            foreach ($request->file('document_paths') as $index => $docFile) {
                if ($docFile->isValid()) {
                    $docFilename = time() . '_doc_' . $index . '_' . uniqid() . '.' . $docFile->getClientOriginalExtension();
                    $docPath = Storage::disk('s3')->putFileAs('candidate_documents', $docFile, $docFilename);
                    $docUrl = Storage::disk('s3')->url($docPath);
                    $uploadedDocuments[] = $docUrl;
                }
            }
        }
        $data['document_paths'] = $uploadedDocuments;
        Log::info($data['employment']);
        $candidateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            
            'date_of_offer' => now()->format('Y-m-d'),
            'address' => $data['current_address'],
            'designation' => $data['position_applied_for'],
            'department' => $data['department'],
            'location' => explode(',', $data['current_address'])[2] ?? '',
            'dob' => $data['dob'],
            // 'doj' => $data['date_of_joining'],
            'ctc' => $data['ctc'] ?? 0,
            'experience' => $data['experience'],
            'last_salary' => $data['last_salary'] ?? 0,
            'document_path' => json_encode($uploadedDocuments),
            'status' => (int) 2,

            // NEW fields storing uploaded data
            'gender' => $data['gender'] ?? null,
            'marital_status' => $data['marital_status'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'language' => $data['language'] ?? null,
            'permanent_address' => $data['permanent_address'] ?? null,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            'education' => $data['education'] ?? null,
            // 'previous_employer' => $data['previous_employer'] ?? null,
            'notice_period' => $data['notice_period'] ?? null,
            'previous_employment' => json_encode($data['employment']) ?? null,
            'disabilities' => $data['disabilities'] ?? null,
            'currently_employed' => $data['currently_employed'] ?? null,
            'reason_for_leaving' => $data['reason_for_leaving'] ?? null,
            'other_info' => $data['other_info'] ?? null,
            'photo' => $data['photo'] ?? null,
            'signature' => $data['signature'] ?? null,
            'photo_name' => $data['photo_name'] ?? null,
            'photo_s3_path' => $data['photo_s3_path'] ?? null,
            'document_paths' => json_encode($data['document_paths'] ?? []),
        ];

        // Create the candidate record
        $candidate = Candidate::find($data['id']); // $id should be defined before

        if ($candidate) {
            $candidate->update($candidateData);
        }

        // Clear the session data
        session()->forget('employee_form_data');

        // Redirect to success page
        return redirect()->route('hrm.success')->with('success', 'Your application has been submitted successfully!');

        } catch (\Throwable $th) {
            Log::error('Error while saving candidate application: ' . $th->getMessage());
        if (
            str_contains($th->getMessage(), 'Integrity constraint violation') &&
            str_contains($th->getMessage(), 'candidates_email_unique')
        ) {
            return redirect()->route('hrm.success')->with('error', 'This email has already been used to submit an application.');
        }

        return redirect()->route('hrm.success')->with('error', 'Something went wrong while submitting your application. Please try again or contact HR.');
    }
        
    }

    public function adminPreview($id){
        $candidate = Candidate::findOrFail($id);
        $education_json = $candidate->education;

    // Ensure it's decoded as an array of entries (preserving keys like "1", "2")
        $education_data = is_string($education_json)
        ? json_decode($education_json, true)
        : $education_json;

        return view('hrm.adminPreview', compact('candidate', 'education_data'));
    }

    public function bulkUpdate(Request $request)
    {
        $candidateIds = $request->input('selected_candidates', []);
        $action = $request->input('action');

        if (empty($candidateIds)) {
            return redirect()->back()->with('error', 'No candidates selected.');
        }

        $responseValue = null;
        if ($action === 'accept') {
            $responseValue = 1;
        } elseif ($action === 'reject') {
            $responseValue = 0;
        }

        Candidate::whereIn('id', $candidateIds)->update(['company_response' => $responseValue]);

        return redirect()->back()->with('success', 'Candidates updated successfully.');
    }


}
