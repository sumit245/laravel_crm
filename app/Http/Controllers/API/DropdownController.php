<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Reference Data API — provides dropdown/lookup data for the mobile app forms. Returns lists of
 * states, cities, districts, and other reference data needed for form population.
 *
 * Data Flow:
 *   GET /api/states → Return state list → GET /api/cities?state_id=X → Return filtered
 *   city list
 *
 * @depends-on State, City
 * @business-domain Mobile API
 * @package App\Http\Controllers\Api
 */
class DropdownController extends Controller
{
    /**
     * 
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  Request  $request  The incoming HTTP request
     * @return JsonResponse  JSON response with result data
     */
    public function fetchState(Request $request): JsonResponse
    {
        $data['states'] = State::get(["name", "id"]);
        return response()->json($data);
    }

    /**
     * Fetch city.
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  Request  $request  The incoming HTTP request
     * @return JsonResponse  JSON response with result data
     */
    public function fetchCity(Request $request): JsonResponse
    {
        $data['cities'] = City::where("state_id", $request->state_id)->get(["name", "id"]);
        return response()->json($data);
    }
}
