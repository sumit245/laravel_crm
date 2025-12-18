<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InventoryDispatchImport implements ToCollection, WithHeadingRow
{
    protected $projectId;
    protected $storeId;

    public function __construct($projectId, $storeId)
    {
        $this->projectId = $projectId;
        $this->storeId = $storeId;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        // Return the collection as-is for validation in controller
        return $collection;
    }
}
