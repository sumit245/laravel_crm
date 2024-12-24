<?php

namespace App\Imports;

use App\Models\City;
use App\Models\Site; // Assuming cities are stored in the 'City' model
use App\Models\State;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiteImport implements ToModel, WithHeadingRow
{
 protected $projectId;

 // Constructor to accept project ID
 public function __construct($projectId)
 {
  $this->projectId = $projectId;
 }

 /**
  * @param array $row
  *
  * @return \Illuminate\Database\Eloquent\Model|null
  */
 public function model(array $row)
 {

  Log::info('Row Data: ', $row);

  // Fetch the district ID based on the district name
  $districtId = $this->getDistrictId($row['district']);
  $stateId    = $this->getStateId($row['state']);
  // If no matching district is found, log an error and skip this row
  if (!$districtId) {
   Log::error('District not found for: ' . $row['district']);
   return null; // Skip this row
  }
  if (!$stateId) {
//    Log::error('State not found for: ' . $row['state']);
   return null; // Skip this row
  }

  return new Site([
   'project_id'       => $this->projectId,
   'site_name'        => $row['site_name'],
   'state'            => $stateId,
   'district'         => $districtId,
   'location'         => $row['location'],
   'project_capacity' => $row['project_capacity'],
   'ca_number'        => $row['ca_number'],
   'contact_no'       => $row['contact_no'],
   'sanction_load'    => $row['sanction_load_in_kwp'],
   'meter_number'     => $row['meter_no'],
  ]);
 }

 /**
  * Fetch the district ID based on the district name using fuzzy matching.
  *
  * @param string $districtName
  * @return int|null
  */
 private function getDistrictId($districtName)
 {
  // Fetch all districts from the database
  $districts = City::pluck('name', 'id')->toArray();

  $closestMatch     = null;
  $shortestDistance = -1;

  foreach ($districts as $id => $name) {
   // Calculate Levenshtein distance
   $levenshteinDistance = levenshtein(strtolower($districtName), strtolower($name));

   // Update closest match if this is the best match so far
   if ($shortestDistance == -1 || $levenshteinDistance < $shortestDistance) {
    $closestMatch     = $id;
    $shortestDistance = $levenshteinDistance;
   }
  }

  // Accept the match only if the distance is within a reasonable threshold (e.g., 3)
  return $shortestDistance <= 3 ? $closestMatch : null;
 }
 private function getStateId($stateName)
 {
  // Fetch all districts from the database
  $states = State::pluck('name', 'id')->toArray();

  $closestMatch     = null;
  $shortestDistance = -1;

  foreach ($states as $id => $name) {
   // Calculate Levenshtein distance
   $levenshteinDistance = levenshtein(strtolower($stateName), strtolower($name));

   // Update closest match if this is the best match so far
   if ($shortestDistance == -1 || $levenshteinDistance < $shortestDistance) {
    $closestMatch     = $id;
    $shortestDistance = $levenshteinDistance;
   }
  }

  // Accept the match only if the distance is within a reasonable threshold (e.g., 3)
  return $shortestDistance <= 3 ? $closestMatch : null;
 }
}
