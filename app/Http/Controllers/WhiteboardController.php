<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meet;
use App\Models\Whiteboard;

/**
 * Collaborative Whiteboard / Notes — provides a shared notes/whiteboard feature for project
 * teams to collaborate on ideas, action items, and quick notes during planning sessions.
 *
 * Data Flow:
 *   Create whiteboard → Add content → Share with team → Edit collaboratively
 *
 * @depends-on Whiteboard, User
 * @business-domain Meetings & Collaboration
 * @package App\Http\Controllers
 */
class WhiteboardController extends Controller
{
     /**
      * 
      *
      * @param  Meet  $reviewMeeting  
      * @return void  
      */
     public function show(Meet $reviewMeeting)
    {
        $whiteboard = $reviewMeeting->whiteboard()->firstOrCreate(
            ['review_meeting_id' => $reviewMeeting->id]
        );
        return response()->json(['data' => $whiteboard->data]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Data flow: HTTP Request → Validation → Database → Redirect with status
     *
     * @param  Request  $request  The incoming HTTP request
     * @param  Meet  $reviewMeeting  
     * @return void  
     */
    public function store(Request $request, Meet $reviewMeeting)
    {
        $whiteboard = $reviewMeeting->whiteboard()->updateOrCreate(
            ['review_meeting_id' => $reviewMeeting->id],
            ['data' => $request->input('data')]
        );

        return response()->json(['success' => true]);
    }
}
