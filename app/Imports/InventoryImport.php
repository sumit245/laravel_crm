<?php

namespace App\Imports;

use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InventoryImport implements ToModel, WithHeadingRow
{
    protected $projectId, $storeId;

    // Constructor to accept project ID
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
