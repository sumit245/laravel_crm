<?php

namespace App\Http\Controllers;

use App\Models\RmsSyncBatch;
use Illuminate\Http\JsonResponse;

class RmsSyncController extends Controller
{
    public function show(RmsSyncBatch $batch): JsonResponse
    {
        return response()->json([
            'batch_id' => $batch->id,
            'status' => $batch->status,
            'total_poles' => $batch->total_poles,
            'processed_poles' => $batch->processed_poles,
            'success_count' => $batch->success_count,
            'error_count' => $batch->error_count,
            'started_at' => $batch->started_at,
            'finished_at' => $batch->finished_at,
            'last_error' => $batch->last_error,
        ]);
    }
}
