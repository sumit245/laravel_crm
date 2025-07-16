<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
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
    public function index(Request $request)
    {
        $query = Candidate::query();

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('designation')) {
            $query->where('designation', $request->designation);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        $candidates = $query->paginate(10)->appends($request->query());

        return view('hrm.index', compact('candidates'));
    }


    public function importCandidates(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,csv']);

        try {
            Excel::import(new CandidatesImport, $request->file('file'));

            return redirect()->back()->with('success', 'Candidates imported successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Check if it's a duplicate entry error
            if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'Duplicate entry')) {
                // Try to extract the duplicate email from the error message
                preg_match("/Duplicate entry '([^']+)'/", $e->getMessage(), $matches);
                $duplicateValue = $matches[1] ?? 'an existing email';

                return redirect()->back()->with('error', "Import failed: Duplicate email found – $duplicateValue");
            }

            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
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

    public function destroy($id)
    {
        $candidate = Candidate::findOrFail($id);
        $candidate->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Deleted successfully']);
        }

        return redirect()->back()->with('success', 'Candidate deleted.');
    }

}
