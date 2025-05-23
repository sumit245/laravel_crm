<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\CandidatesImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Mail\CandidateMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Candidate;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser; // Install with: composer require smalot/pdfparser

class CandidateController extends Controller
{
    //
    public function index()
    {
        $candidates = Candidate::paginate(10); // Paginate results
        return view('hrm.index', compact('candidates')); // Create this Blade file
    }

    public function importCandidates(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv']);

        Excel::import(new CandidatesImport, $request->file('file'));

        return redirect()->back()->with('success', 'Candidates imported successfully.');
    }

    public function sendEmails()
    {
        $candidates = Candidate::where('status', 'pending')->get();

        foreach ($candidates as $candidate) {
            Mail::to($candidate->email)->send(new CandidateMail($candidate));
            $candidate->update(['status' => 'emailed']);
        }

        return redirect()->back()->with('success', 'Emails sent successfully.');
    }

    public function showUploadForm($id)
    {
        $candidate = Candidate::findOrFail($id);
        return view('upload', compact('candidate'));
    }
    public function uploadDocuments(Request $request, $id)
    {
        $candidate = Candidate::findOrFail($id);

        $request->validate([
            'documents' => 'required',
            'documents.*' => 'mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $extractedData = [];

        foreach ($request->file('documents') as $file) {
            $path = $file->store('documents', 'public');

            // If the file is a PDF, extract text
            if ($file->getClientOriginalExtension() === 'pdf') {
                $parser = new Parser();
                $pdf = $parser->parseFile(storage_path('app/public/' . $path));
                $text = $pdf->getText();

                // Extract information using regex (modify as needed)
                if (preg_match('/Name:\s*(.+)/', $text, $matches)) {
                    $extractedData['full_name'] = $matches[1];
                }
                if (preg_match('/Address:\s*(.+)/', $text, $matches)) {
                    $extractedData['address'] = $matches[1];
                }
                if (preg_match('/Experience:\s*(\d+)/', $text, $matches)) {
                    $extractedData['experience'] = $matches[1];
                }
                if (preg_match('/Salary:\s*₹?([\d,]+)/', $text, $matches)) {
                    $extractedData['last_salary'] = str_replace(',', '', $matches[1]);
                }
            }
        }

        // Update candidate details
        $candidate->update(array_filter($extractedData));

        return redirect()->back()->with('success', 'Documents uploaded and details extracted successfully.');
    }
}
