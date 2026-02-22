<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\UserCategory;

/**
 * Service layer for conveyance/travel claim operations. Handles business rules for fare
 * calculation, validation, and status management.
 *
 * Data Flow:
 *   Controller delegates → Service applies business rules → Model operations → Return
 *   result
 *
 * @depends-on Conveyance, Vehicle, User
 * @business-domain Finance & Expense
 * @package App\Services
 */
class ConveyanceService
{
    /**
     * Get allowed vehicles for a specific user based on their category.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllowedVehicles(int $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return collect([]);
        }

        // Fetch UserCategory by the code stored in user's category column (e.g., 'M5')
        $userCategory = UserCategory::where('category_code', $user->category)->first();

        // If no category found or no allowed_vehicles defined, return empty
        if (!$userCategory || empty($userCategory->allowed_vehicles)) {
            return collect([]);
        }

        // allowed_vehicles is stored as a JSON string of IDs, e.g., ["1","3"]
        $allowedVehicleIds = $userCategory->allowed_vehicles;
        
        // Handle case where it might already be cast to array by Eloquent if casts are defined, 
        // otherwise decode it.
        if (is_string($allowedVehicleIds)) {
            $allowedVehicleIds = json_decode($allowedVehicleIds, true);
        }

        if (!is_array($allowedVehicleIds)) {
            return collect([]);
        }

        return Vehicle::whereIn('id', $allowedVehicleIds)->get();
    }
}
