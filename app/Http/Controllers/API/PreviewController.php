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
        $validatedData = $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|max:255',
            'phone' => 'string|max:15',
            'dob' => 'date',
            'gender' => 'string',
            'marital_status' => 'string',
            'nationality' => 'string|max:50',
            'language' => 'string|max:50',
            
            // Contact Information
            'perm_house_no' => 'string|max:50',
            'perm_street' => 'string|max:100',
            'perm_city' => 'string|max:50',
            'perm_state' => 'string|max:50',
            'perm_country' => 'string|max:50',
            'perm_zip' => 'string|max:10',
            
            'curr_house_no' => 'string|max:50',
            'curr_street' => 'string|max:100',
            'curr_city' => 'string|max:50',
            'curr_state' => 'string|max:50',
            'curr_country' => 'string|max:50',
            'curr_zip' => 'string|max:10',
            
            'emergency_contact_name' => 'string|max:255',
            'emergency_contact_phone' => 'string|max:15',
            
            // Education (optional as it's dynamic)
            'education' => 'nullable|array',
            
            // Employment Details
            'position_applied_for' => 'string|max:100',
            'department' => 'string|max:100',
            'date_of_joining' => 'date',
            'previous_employer' => 'string|max:255',
            'experience' => 'numeric',
            'notice_period' => 'string|max:50',
            
            // Documents - now we're handling file paths directly
            'document_name' => 'nullable|array',
            'document_s3_path' => 'nullable|array',
            
            // Additional Information
            'disabilities' => 'string|in:Yes,No',
            'currently_employed' => 'string|in:Yes,No',
            'reason_for_leaving' => 'nullable|string|max:255',
            'other_info' => 'nullable|string',
            
            // Photo - now we're handling file paths directly
            'passport_photo_name' => 'string',
            'passport_photo_s3_path' => 'string',
            
            // Declaration
            'signature' => 'string|max:255',
            'date' => 'date',
            'agree_terms' => 'accepted',
        ]);

        // Create a data array
        $data = $validatedData;
        
        // Format addresses
        $data['permanent_address'] = "{$data['perm_house_no']}, {$data['perm_street']}, {$data['perm_city']}, {$data['perm_state']}, {$data['perm_country']} - {$data['perm_zip']}";
        $data['current_address'] = "{$data['curr_house_no']}, {$data['curr_street']}, {$data['curr_city']}, {$data['curr_state']}, {$data['curr_country']} - {$data['curr_zip']}";
        
        // Process documents - we're now receiving S3 paths directly
        $documents = [];
        if (isset($data['document_name']) && isset($data['document_s3_path'])) {
            foreach ($data['document_name'] as $index => $name) {
                if (isset($data['document_s3_path'][$index]) && !empty($data['document_s3_path'][$index])) {
                    $documents[] = [
                        'name' => $name,
                        's3_path' => $data['document_s3_path'][$index]
                    ];
                }
            }
            $data['documents'] = $documents;
        }
        
        // Remove the raw document arrays from data
        unset($data['document_name']);
        unset($data['document_s3_path']);
        
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
            
            // Photo information
            $candidate->photo_name = $data['passport_photo_name'] ?? null;
            $candidate->photo_s3_path = $data['passport_photo_s3_path'] ?? null;
            
            $candidate->signature = $data['signature'] ?? null;
            
            // Store education as JSON
            if (isset($data['education'])) {
                $candidate->education = json_encode($data['education']);
            }
            
            // Store document paths as JSON
            if (isset($data['documents'])) {
                $candidate->document_paths = json_encode($data['documents']);
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

    // Method to generate a presigned S3 URL for direct uploads
    public function getS3UploadUrl(Request $request)
    {
        $request->validate([
            'file_name' => 'string',
            'file_type' => 'string'
        ]);

        $fileName = $request->file_name;
        $fileType = $request->file_type;
        
        // Generate a unique file name
        $uniqueFileName = Str::uuid() . '-' . $fileName;
        
        // Determine folder based on file type
        $folder = str_contains($fileType, 'image') ? 'photos' : 'documents';
        
        // Generate the S3 path
        $filePath = $folder . '/' . $uniqueFileName;
        
        // Generate a presigned URL for direct upload to S3
        $s3Client = Storage::disk('s3')->getClient();
        $bucket = config('filesystems.disks.s3.bucket');
        
        $command = $s3Client->getCommand('PutObject', [
            'Bucket' => $bucket,
            'Key' => $filePath,
            'ContentType' => $fileType,
            'ACL' => 'public-read'
        ]);
        
        $presignedUrl = $s3Client->createPresignedRequest($command, '+60 minutes')->getUri()->__toString();
        
        return response()->json([
            'upload_url' => $presignedUrl,
            's3_path' => $filePath,
            'file_name' => $uniqueFileName
        ]);
    }
}