<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TasksExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $query;
    protected $projectType;

    public function __construct($query, $projectType)
    {
        $this->query = $query;
        $this->projectType = $projectType;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        if ($this->projectType == 1) {
            return [
                'ID',
                'Panchayat',
                'Block',
                'District',
                'Engineer',
                'Vendor',
                'Manager',
                'Status',
                'Start Date',
                'End Date',
                'Billed',
                'Description',
            ];
        }

        return [
            'ID',
            'Task Name',
            'Activity',
            'Site Name',
            'Engineer',
            'Vendor',
            'Manager',
            'Status',
            'Start Date',
            'End Date',
            'Approved By',
            'Description',
        ];
    }

    public function map($task): array
    {
        if ($this->projectType == 1) {
            // Streetlight tasks map
            return [
                $task->id,
                $task->site->panchayat ?? 'N/A',
                $task->site->block ?? 'N/A',
                $task->site->district ?? 'N/A',
                $task->engineer ? ($task->engineer->firstName . ' ' . $task->engineer->lastName) : 'N/A',
                $task->vendor ? $task->vendor->name : 'N/A',
                $task->manager ? ($task->manager->firstName . ' ' . $task->manager->lastName) : 'N/A',
                $task->status ?? 'N/A',
                $task->start_date ? $task->start_date->format('Y-m-d') : 'N/A',
                $task->end_date ? $task->end_date->format('Y-m-d') : 'N/A',
                $task->billed ? 'Yes' : 'No',
                $task->description ?? 'N/A',
            ];
        } else {
            // Rooftop/Other tasks map
            return [
                $task->id,
                $task->task_name ?? 'N/A',
                $task->activity ?? 'N/A',
                $task->site->site_name ?? 'N/A',
                $task->engineer ? ($task->engineer->firstName . ' ' . $task->engineer->lastName) : 'N/A',
                $task->vendor ? $task->vendor->name : 'N/A',
                $task->manager ? ($task->manager->firstName . ' ' . $task->manager->lastName) : 'N/A',
                $task->status ?? 'N/A',
                $task->start_date ? $task->start_date->format('Y-m-d') : 'N/A',
                $task->end_date ? $task->end_date->format('Y-m-d') : 'N/A',
                $task->approved_by ?? 'N/A',
                $task->description ?? 'N/A',
            ];
        }
    }
}
