<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meet;
use App\Models\Whiteboard;

class WhiteboardController extends Controller
{
    //
     public function show(Meet $reviewMeeting)
    {
        $whiteboard = $reviewMeeting->whiteboard()->firstOrCreate(
            ['review_meeting_id' => $reviewMeeting->id]
        );
        return response()->json(['data' => $whiteboard->data]);
    }

    public function store(Request $request, Meet $reviewMeeting)
    {
        $whiteboard = $reviewMeeting->whiteboard()->updateOrCreate(
            ['review_meeting_id' => $reviewMeeting->id],
            ['data' => $request->input('data')]
        );

        return response()->json(['success' => true]);
    }
}
