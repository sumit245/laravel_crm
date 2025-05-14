<?php

namespace App\Imports;

use Carbon\Carbon;
use App\Models\Candidate;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CandidatesImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Candidate([
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'date_of_offer' => $this->convertDate($row['date_of_offer']) ?? "N/A",
            'address' => $row['address'] ?? "N/A",
            'designation' => $row['designation'] ?? "N/A",
            'department' => $row['department'] ?? "N/A",
            'location' => $row['location'] ?? "N/A",
            'doj' => $this->convertDate($row['doj']) ?? "N/A",
            'ctc' => $row['ctc'] ?? "N/A",
            // 'experience' => $row['experience'] ?? "N/A",
        ]);
    }
    // Helper function to parse date
    private function convertDate($date)
    {
        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null; // Handle invalid dates gracefully
        }
    }
}
