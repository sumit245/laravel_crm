<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\State;
use App\Models\City;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class DropdownController extends Controller
{
    //
    public function fetchState(Request $request): JsonResponse
    {
        $data['states'] = State::get(["name", "id"]);
        return response()->json($data);
    }

    public function fetchCity(Request $request): JsonResponse
    {
        $data['cities'] = City::where("state_id", $request->state_id)->get(["name", "id"]);
        return response()->json($data);
    }
}
