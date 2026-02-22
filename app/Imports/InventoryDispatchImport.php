<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Excel importer for bulk inventory dispatch. Allows admins to dispatch multiple items to a
 * vendor at once by uploading an Excel file with serial numbers.
 *
 * Data Flow:
 *   Excel upload → Parse serial numbers → Validate availability → Create
 *   InventoryDispatch records → Decrement quantities
 *
 * @depends-on InventoryDispatch, InventroyStreetLightModel
 * @business-domain Inventory & Warehouse
 * @package App\Imports
 */
class InventoryDispatchImport implements ToCollection, WithHeadingRow
{
    protected $projectId;
    protected $storeId;

    /**
     * Create a new InventoryDispatchImport instance.
     *
     * @param  mixed  $projectId  The project identifier
     * @param  mixed  $storeId  The store identifier
     */
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
