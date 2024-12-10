<?php

namespace App\Imports;

use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\ToModel;

class InventoryImport implements ToModel
{
 /**
  * @param array $row
  *
  * @return \Illuminate\Database\Eloquent\Model|null
  */
 public function model(array $row)
 {
  return new Inventory([
   //
   'productName' => $row[0], // Map column A
   'brand' => $row[1], // Map column B
   'description' => $row[2], // Map column C
   'unit' => $row[3], // Map column D
   'initialQuantity' => $row[4], // Map column E
   'quantityStock' => $row[5], // Map column F
//    'receivedDate' => $row[6], // Map column G
  ]);
 }
}
