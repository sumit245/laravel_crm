<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StaffImportFormatExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private ?array $rows = null)
    {
    }

    public function collection()
    {
        return new Collection($this->rows ?? [
            [
                'Rahul',
                'Raj',
                'rahul.raj@example.com',
                '',
                '9876543210',
                'Site Engineer',
                '',
                'Streetlight Project',
                'Field',
                'Operations',
                'Pintu Chaudhary',
                '',
                'Patna, Bihar',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'first_name',
            'last_name',
            'email',
            'password',
            'contact_number',
            'role',
            'project_id',
            'project',
            'category',
            'department',
            'reporting_manager',
            'vertical_head',
            'address',
        ];
    }
}
