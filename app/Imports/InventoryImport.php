<?php

namespace App\Imports;

use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Excel importer for rooftop project inventory. Parses GRN (Goods Received Note) Excel files to
 * bulk-add inventory items to a store.
 *
 * Data Flow:
 *   Excel upload → Parse rows → Validate item codes + serials → Create Inventory records
 *   with project_id + store_id
 *
 * @depends-on Inventory
 * @business-domain Inventory & Warehouse
 * @package App\Imports
 */
class InventoryImport implements ToModel, WithHeadingRow
{
    protected $projectId, $storeId;

    /**
     * Constructor to accept project ID
     *
     * @param  mixed  $projectId  The project identifier
     * @param  mixed  $storeId  The store identifier
     */
    public function __construct($projectId, $storeId)
    {
        $this->projectId = $projectId;
        $this->storeId   = $storeId;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Inventory([
            //
            'project_id' => $this->projectId,
            'store_id'   => $this->storeId,
            'category'   => $row['category'], // Map column B
            'sub_category' => $row['sub_category'], // Map column B
            'productName' => $row['item_description'], // Map column A
            'unit' => $row['unit'], // Map column D
            'initialQuantity' => $row['quantity'], // Map column E
            'rate' => $row['rate'], // Map column D
            'total' => $row['total'],
        ]);
    }
}
